#!/usr/bin/env python3
"""
CyberLab Academy - Lab Manager
Manages lab lifecycle: creation, monitoring, termination
"""

import asyncio
import logging
from datetime import datetime, timedelta
from typing import Optional, Dict, List
from dataclasses import dataclass
from enum import Enum

import docker
import redis
import psycopg2
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Initialize FastAPI
app = FastAPI(title="CyberLab Lab Manager")

# Initialize clients
docker_client = docker.from_env()
redis_client = redis.Redis(host='redis', port=6379, db=0, decode_responses=True)


class LabStatus(str, Enum):
    PENDING = "pending"
    STARTING = "starting"
    RUNNING = "running"
    STOPPED = "stopped"
    EXPIRED = "expired"
    ERROR = "error"


class LabType(str, Enum):
    SINGLE = "single"
    MULTI = "multi"
    WEB = "web"
    NETWORK = "network"


@dataclass
class LabConfig:
    lab_id: str
    user_id: str
    lab_type: LabType
    machine_image: str
    cpu_limit: float = 2.0
    memory_limit: str = "2g"
    disk_limit: str = "20g"
    time_limit_hours: int = 4
    network_mode: str = "isolated"


@dataclass
class LabInstance:
    lab_id: str
    user_id: str
    container_id: Optional[str]
    status: LabStatus
    ip_address: Optional[str]
    ssh_port: int = 22
    web_port: int = 80
    created_at: datetime
    expires_at: datetime
    flag: str


class LabManager:
    """Manages lab lifecycle operations"""
    
    def __init__(self):
        self.docker = docker_client
        self.redis = redis_client
        
    async def create_lab(self, config: LabConfig) -> LabInstance:
        """Create a new lab instance"""
        logger.info(f"Creating lab {config.lab_id} for user {config.user_id}")
        
        try:
            # Generate flag
            flag = self._generate_flag()
            
            # Create isolated network
            network = self._create_network(config.lab_id)
            
            # Create container
            container = self._create_container(config, flag, network)
            
            # Wait for container to be ready
            self._wait_for_ready(container)
            
            # Get container IP
            ip_address = self._get_container_ip(container, network)
            
            # Calculate expiration time
            expires_at = datetime.utcnow() + timedelta(hours=config.time_limit_hours)
            
            # Create lab instance
            instance = LabInstance(
                lab_id=config.lab_id,
                user_id=config.user_id,
                container_id=container.id,
                status=LabStatus.RUNNING,
                ip_address=ip_address,
                created_at=datetime.utcnow(),
                expires_at=expires_at,
                flag=flag
            )
            
            # Store in Redis
            self._store_lab_instance(instance)
            
            # Store in database
            self._store_in_database(instance, config)
            
            logger.info(f"Lab {config.lab_id} created successfully")
            return instance
            
        except Exception as e:
            logger.error(f"Failed to create lab {config.lab_id}: {e}")
            raise
    
    async def stop_lab(self, lab_id: str) -> bool:
        """Stop a running lab"""
        logger.info(f"Stopping lab {lab_id}")
        
        try:
            instance = self._get_lab_instance(lab_id)
            if not instance:
                return False
            
            # Stop container
            if instance.container_id:
                try:
                    container = self.docker.containers.get(instance.container_id)
                    container.stop(timeout=30)
                    container.remove(force=True)
                except docker.errors.NotFound:
                    pass
            
            # Remove network
            self._remove_network(lab_id)
            
            # Update status
            instance.status = LabStatus.STOPPED
            self._update_lab_status(instance)
            
            # Remove from Redis
            self.redis.delete(f"lab:{lab_id}")
            
            logger.info(f"Lab {lab_id} stopped successfully")
            return True
            
        except Exception as e:
            logger.error(f"Failed to stop lab {lab_id}: {e}")
            return False
    
    async def reset_lab(self, lab_id: str) -> Optional[LabInstance]:
        """Reset a lab to its initial state"""
        logger.info(f"Resetting lab {lab_id}")
        
        try:
            instance = self._get_lab_instance(lab_id)
            if not instance:
                return None
            
            # Get original config
            config = self._get_lab_config(lab_id)
            
            # Stop existing lab
            await self.stop_lab(lab_id)
            
            # Create new lab
            new_instance = await self.create_lab(config)
            
            logger.info(f"Lab {lab_id} reset successfully")
            return new_instance
            
        except Exception as e:
            logger.error(f"Failed to reset lab {lab_id}: {e}")
            return None
    
    async def validate_flag(self, lab_id: str, submitted_flag: str) -> bool:
        """Validate a submitted flag"""
        instance = self._get_lab_instance(lab_id)
        if not instance:
            return False
        
        # Compare flags (case-insensitive)
        return instance.flag.lower() == submitted_flag.lower()
    
    async def monitor_labs(self):
        """Monitor running labs and terminate expired ones"""
        while True:
            try:
                # Get all running labs
                lab_keys = self.redis.keys("lab:*")
                
                for key in lab_keys:
                    lab_data = self.redis.hgetall(key)
                    if not lab_data:
                        continue
                    
                    lab_id = lab_data.get('lab_id')
                    expires_at = datetime.fromisoformat(lab_data.get('expires_at'))
                    
                    # Check if lab is expired
                    if datetime.utcnow() > expires_at:
                        logger.info(f"Lab {lab_id} expired, stopping...")
                        await self.stop_lab(lab_id)
                    
                    # Check container health
                    container_id = lab_data.get('container_id')
                    if container_id:
                        try:
                            container = self.docker.containers.get(container_id)
                            if container.status != 'running':
                                logger.warning(f"Lab {lab_id} container not running")
                                # Attempt restart
                                container.restart()
                        except docker.errors.NotFound:
                            logger.error(f"Lab {lab_id} container not found")
                            await self.stop_lab(lab_id)
                
            except Exception as e:
                logger.error(f"Error in monitor loop: {e}")
            
            # Sleep for 30 seconds
            await asyncio.sleep(30)
    
    def _generate_flag(self) -> str:
        """Generate a unique flag"""
        import uuid
        return f"FLAG{{{uuid.uuid4().hex[:32].upper()}}}"
    
    def _create_network(self, lab_id: str):
        """Create an isolated network for the lab"""
        network_name = f"lab-{lab_id}"
        
        # Check if network exists
        try:
            existing = self.docker.networks.get(network_name)
            existing.remove(force=True)
        except docker.errors.NotFound:
            pass
        
        # Create new network
        network = self.docker.networks.create(
            network_name,
            driver="overlay",
            internal=True,
            labels={"lab_id": lab_id}
        )
        
        return network
    
    def _create_container(self, config: LabConfig, flag: str, network):
        """Create a new container for the lab"""
        
        # Prepare environment
        environment = {
            "LAB_ID": config.lab_id,
            "USER_ID": config.user_id,
            "FLAG": flag,
            "EXPIRES_AT": (datetime.utcnow() + timedelta(hours=config.time_limit_hours)).isoformat()
        }
        
        # Prepare resource limits
        resource_limits = {
            "cpu_quota": int(config.cpu_limit * 100000),
            "cpu_period": 100000,
            "memory": config.memory_limit,
            "storage_opt": {"size": config.disk_limit}
        }
        
        # Create container
        container = self.docker.containers.run(
            image=config.machine_image,
            name=f"lab-{config.lab_id}",
            environment=environment,
            detach=True,
            network=network.name,
            labels={
                "lab_id": config.lab_id,
                "user_id": config.user_id,
                "managed_by": "cyberlab"
            },
            security_opt=[
                "no-new-privileges:true",
                "seccomp=unconfined"
            ],
            cap_drop=["ALL"],
            cap_add=["NET_BIND_SERVICE"],
            read_only=False,
            tmpfs={"/tmp": "size=100M"},
            **resource_limits
        )
        
        return container
    
    def _wait_for_ready(self, container, timeout: int = 60):
        """Wait for container to be ready"""
        import time
        start_time = time.time()
        
        while time.time() - start_time < timeout:
            container.reload()
            if container.status == 'running':
                # Check if SSH is ready
                try:
                    exec_result = container.exec_run("ss -tln | grep :22")
                    if exec_result.exit_code == 0:
                        return True
                except:
                    pass
            time.sleep(2)
        
        raise TimeoutError(f"Container not ready after {timeout} seconds")
    
    def _get_container_ip(self, container, network) -> str:
        """Get container IP address"""
        container.reload()
        network_info = container.attrs['NetworkSettings']['Networks']
        
        for net_name, net_info in network_info.items():
            if net_name == network.name:
                return net_info['IPAddress']
        
        raise ValueError("Container not connected to network")
    
    def _remove_network(self, lab_id: str):
        """Remove lab network"""
        network_name = f"lab-{lab_id}"
        try:
            network = self.docker.networks.get(network_name)
            network.remove(force=True)
        except docker.errors.NotFound:
            pass
    
    def _store_lab_instance(self, instance: LabInstance):
        """Store lab instance in Redis"""
        key = f"lab:{instance.lab_id}"
        data = {
            "lab_id": instance.lab_id,
            "user_id": instance.user_id,
            "container_id": instance.container_id or "",
            "status": instance.status,
            "ip_address": instance.ip_address or "",
            "ssh_port": instance.ssh_port,
            "web_port": instance.web_port,
            "created_at": instance.created_at.isoformat(),
            "expires_at": instance.expires_at.isoformat(),
            "flag": instance.flag
        }
        self.redis.hset(key, mapping=data)
        self.redis.expire(key, instance.time_limit_hours * 3600)
    
    def _get_lab_instance(self, lab_id: str) -> Optional[LabInstance]:
        """Get lab instance from Redis"""
        key = f"lab:{lab_id}"
        data = self.redis.hgetall(key)
        
        if not data:
            return None
        
        return LabInstance(
            lab_id=data['lab_id'],
            user_id=data['user_id'],
            container_id=data.get('container_id') or None,
            status=LabStatus(data['status']),
            ip_address=data.get('ip_address') or None,
            ssh_port=int(data.get('ssh_port', 22)),
            web_port=int(data.get('web_port', 80)),
            created_at=datetime.fromisoformat(data['created_at']),
            expires_at=datetime.fromisoformat(data['expires_at']),
            flag=data['flag']
        )
    
    def _update_lab_status(self, instance: LabInstance):
        """Update lab status in Redis"""
        key = f"lab:{instance.lab_id}"
        self.redis.hset(key, "status", instance.status)
    
    def _store_in_database(self, instance: LabInstance, config: LabConfig):
        """Store lab instance in PostgreSQL"""
        conn = psycopg2.connect(
            host="postgresql",
            database="cyberlab_labs",
            user="cyberlab",
            password="secret"
        )
        
        try:
            cur = conn.cursor()
            cur.execute("""
                INSERT INTO lab_instances 
                (lab_id, user_id, container_id, status, ip_address, ssh_port, web_port, expires_at)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
            """, (
                instance.lab_id,
                instance.user_id,
                instance.container_id,
                instance.status,
                instance.ip_address,
                instance.ssh_port,
                instance.web_port,
                instance.expires_at
            ))
            conn.commit()
        finally:
            conn.close()
    
    def _get_lab_config(self, lab_id: str) -> LabConfig:
        """Get lab configuration from database"""
        conn = psycopg2.connect(
            host="postgresql",
            database="cyberlab_labs",
            user="cyberlab",
            password="secret"
        )
        
        try:
            cur = conn.cursor()
            cur.execute("SELECT * FROM labs WHERE id = %s", (lab_id,))
            row = cur.fetchone()
            
            if not row:
                raise ValueError(f"Lab {lab_id} not found")
            
            return LabConfig(
                lab_id=row[0],
                user_id=row[1],
                lab_type=LabType(row[2]),
                machine_image=row[3],
                cpu_limit=float(row[4]),
                memory_limit=row[5],
                disk_limit=row[6],
                time_limit_hours=int(row[7])
            )
        finally:
            conn.close()


# Initialize lab manager
lab_manager = LabManager()


# API Routes

class CreateLabRequest(BaseModel):
    lab_id: str
    user_id: str
    lab_type: str
    machine_image: str
    cpu_limit: float = 2.0
    memory_limit: str = "2g"
    disk_limit: str = "20g"
    time_limit_hours: int = 4


@app.post("/labs")
async def create_lab(request: CreateLabRequest):
    """Create a new lab instance"""
    config = LabConfig(
        lab_id=request.lab_id,
        user_id=request.user_id,
        lab_type=LabType(request.lab_type),
        machine_image=request.machine_image,
        cpu_limit=request.cpu_limit,
        memory_limit=request.memory_limit,
        disk_limit=request.disk_limit,
        time_limit_hours=request.time_limit_hours
    )
    
    instance = await lab_manager.create_lab(config)
    
    return {
        "lab_id": instance.lab_id,
        "status": instance.status,
        "ip_address": instance.ip_address,
        "ssh_port": instance.ssh_port,
        "web_port": instance.web_port,
        "expires_at": instance.expires_at.isoformat(),
        "connection": f"ssh lab@{instance.ip_address} -p {instance.ssh_port}"
    }


@app.post("/labs/{lab_id}/stop")
async def stop_lab(lab_id: str):
    """Stop a running lab"""
    success = await lab_manager.stop_lab(lab_id)
    if not success:
        raise HTTPException(status_code=404, detail="Lab not found")
    return {"status": "stopped"}


@app.post("/labs/{lab_id}/reset")
async def reset_lab(lab_id: str):
    """Reset a lab to its initial state"""
    instance = await lab_manager.reset_lab(lab_id)
    if not instance:
        raise HTTPException(status_code=404, detail="Lab not found")
    return {
        "lab_id": instance.lab_id,
        "status": instance.status,
        "ip_address": instance.ip_address
    }


@app.post("/labs/{lab_id}/validate")
async def validate_flag(lab_id: str, flag: str):
    """Validate a submitted flag"""
    is_valid = await lab_manager.validate_flag(lab_id, flag)
    return {"valid": is_valid}


@app.get("/labs/{lab_id}")
async def get_lab(lab_id: str):
    """Get lab instance details"""
    instance = lab_manager._get_lab_instance(lab_id)
    if not instance:
        raise HTTPException(status_code=404, detail="Lab not found")
    return {
        "lab_id": instance.lab_id,
        "status": instance.status,
        "ip_address": instance.ip_address,
        "expires_at": instance.expires_at.isoformat()
    }


@app.get("/health")
async def health_check():
    """Health check endpoint"""
    return {"status": "healthy"}


@app.on_event("startup")
async def startup_event():
    """Start background tasks"""
    asyncio.create_task(lab_manager.monitor_labs())


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8080)
