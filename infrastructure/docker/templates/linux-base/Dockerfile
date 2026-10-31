FROM ubuntu:22.04

LABEL maintainer="CyberLab Academy"
LABEL description="Base Linux machine template"

# Prevent interactive prompts
ENV DEBIAN_FRONTEND=noninteractive

# Update and install base packages
RUN apt-get update && apt-get install -y \
    sudo \
    curl \
    wget \
    git \
    vim \
    nano \
    htop \
    net-tools \
    iputils-ping \
    nmap \
    tcpdump \
    wireshark-common \
    python3 \
    python3-pip \
    openssh-server \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Create lab user
RUN useradd -m -s /bin/bash -G sudo lab && \
    echo "lab:lab123" | chpasswd && \
    echo "lab ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

# Configure SSH
RUN mkdir /var/run/sshd && \
    echo "PermitRootLogin no" >> /etc/ssh/sshd_config && \
    echo "PasswordAuthentication yes" >> /etc/ssh/sshd_config

# Create flag directory
RUN mkdir -p /opt/flags && \
    chown lab:lab /opt/flags

# Create startup script
COPY scripts/startup.sh /opt/startup.sh
RUN chmod +x /opt/startup.sh

# Expose SSH
EXPOSE 22

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD systemctl is-active ssh || exit 1

# Start services
CMD ["/opt/startup.sh"]
