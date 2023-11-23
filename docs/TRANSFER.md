## Transfer

For start command for withdrawal from processing need run script 

```php
php artisan withdrawal:loop
```

To create a systemd service for a PHP artisan script, you can follow these steps:

1.Create a systemd service unit file for your PHP script:

```bash
sudo vim /etc/systemd/system/transfer-to-processing.service
```
2. Add the following content to the transfer-to-processing.service file, adjusting the ExecStart path to your artisan script and specifying the working directory and user:
```
[Unit]
Description=Transfer to Processing
After=network.target

[Service]
Type=simple
User=server

WorkingDirectory=/home/server
ExecStart=/usr/bin/php /home/server/backend/www/artisan withdrawal:loop

Restart=on-failure
RestartSec=3
StandardOutput=syslog
StandardError=syslog
SyslogIdentifier=your-service-name

[Install]
WantedBy=multi-user.target

```
3. Save the file and exit the text editor.
4. Reload the systemd manager to read the new service configuration:
```bash
sudo systemctl daemon-reload
```
5. Start the systemd service:
```bash
sudo systemctl start transfer-to-processing
```
6. Enable the service to start on boot:
```bash 
sudo systemctl enable transfer-to-processing
```
