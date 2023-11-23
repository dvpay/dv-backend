#Telegram

DV Pay you can install telegram bot

1. Add you telegram bot credentials

```
TELEGRAM_URL=https://api.telegram.org/bot
TELEGRAM_BOT=TelegramNameBOt
TELEGRAM_TOKEN=token
TELEGRAM_WEBHOOK_URL=https://api.merchant.local/telegram/command
```
telegram/command - this route for callback you can modify him in routes/api.php

2. You need install webhook to telegram run command: 

```
php artisan telegram:webhook:set
```
3. If you modify command menu in telegram bot on 
   \App\Services\Telegram\TelegramService::setCommands
after update code menu you need run command manually

```
php artisan telegram:set:buttons
```

Telegram notification use queue priority names notifications if you use supervisor for laravel queue modify config 
additional priority --queue=default,notifications

example:
```
[program:laravel-worker]
command=php /home/server/backend/www/artisan queue:work --queue=default,notifications
process_name=%(program_name)s_%(process_num)02d
numprocs=8
priority=999
autostart=true
autorestart=true
startsecs=1
startretries=3
user=server
redirect_stderr=true
stdout_logfile=/home/server/backend/www/storage/logs/supervisord.log
```

worked notifications:

1. System Errors
2. Webhook Errors
3. Receiving Payment
4. Invoice Creation
5. Transfers
6. Sharp Exchange Rate Change
7. Webhook Sends
8. Daily Report
9. Weekly Report
10. Monthly Report

all notification settings from frontend app

![Telegram_1](./assets/images/telegram_1.png)
![Telegram_1](./assets/images/telegram_2.png)

