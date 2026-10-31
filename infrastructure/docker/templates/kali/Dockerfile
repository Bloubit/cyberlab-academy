FROM linux-base:latest

LABEL description="Kali Linux security tools template"

# Install security tools
RUN apt-get update && apt-get install -y \
    kali-linux-headless \
    kali-tools-web \
    kali-tools-exploitation \
    kali-tools-forensics \
    kali-tools-crypto \
    kali-tools-sniffing-spoofing \
    && rm -rf /var/lib/apt/lists/*

# Install additional tools
RUN pip3 install \
    sqlmap \
    wpscan \
    nikto

# Create tool shortcuts
RUN echo '#!/bin/bash\necho "Available tools: nmap, sqlmap, nikto, wpscan, hydra, john, hashcat"' > /usr/local/bin/tools && \
    chmod +x /usr/local/bin/tools

# Configure lab user
RUN usermod -aG sudo lab

EXPOSE 22
CMD ["/opt/startup.sh"]
