FROM ubuntu:22.04

LABEL description="DVWA - Damn Vulnerable Web Application"

ENV DEBIAN_FRONTEND=noninteractive

# Install dependencies
RUN apt-get update && apt-get install -y \
    apache2 \
    php \
    php-mysql \
    php-gd \
    libapache2-mod-php \
    mysql-server \
    && rm -rf /var/lib/apt/lists/*

# Download and install DVWA
RUN cd /var/www/html && \
    git clone https://github.com/digininja/DVWA.git && \
    mv DVWA/* . && \
    rmdir DVWA && \
    chown -R www-data:www-data /var/www/html

# Configure DVWA
RUN cp /var/www/html/config/config.inc.php.dist /var/www/html/config/config.inc.php

# Configure MySQL
RUN service mysql start && \
    mysql -e "CREATE USER 'dvwa'@'localhost' IDENTIFIED BY 'p@ssw0rd';" && \
    mysql -e "GRANT ALL PRIVILEGES ON *.* TO 'dvwa'@'localhost';" && \
    mysql -e "FLUSH PRIVILEGES;"

# Create flag
RUN echo "FLAG{dvwa_m4st3r_0f_3xplo1ts}" > /opt/flags/flag.txt && \
    chmod 444 /opt/flags/flag.txt

# Startup script
COPY scripts/startup.sh /opt/startup.sh
RUN chmod +x /opt/startup.sh

EXPOSE 80 22
CMD ["/opt/startup.sh"]
