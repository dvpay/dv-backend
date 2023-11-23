<?php
include 'getResult.php';
set_time_limit(0);
$result = getResult();
$is_supported = $result['is_supported'];

if ($is_supported && $result['requirements']['composer']['install']) {
    $sub_folder = str_replace('index.php', '', $_SERVER['PHP_SELF']);
    $sub_folder = str_replace('install', '', $sub_folder);
    $base_url = rtrim($sub_folder, '/');
    $base_url = $base_url ? $base_url . '/' : '/';
}
if (isset($_POST['composer'])) {
    composerInstall();
    echo '<script>window.location = "/setup/"</script>';
}

function composerInstall()
{
    exec('cd ../../ && composer install --no-interaction 2>&1');
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <link rel="stylesheet" type="text/css" href="../css/app.css"/>
    <link rel="stylesheet" type="text/css"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>

</head>
<body>
<div id="progress" style="display: none">
    <div class="d-table w-100 large-loader-container">
        <div class="d-table-cell align-middle">
            <div class="loader"></div>
            <h3 class="mt-3 text-center">Install In progress...</h3>
        </div>
    </div>
</div>
<div id="welcome">
    <div class="p-3">
        <div class="row justify-content-center">
            <div class="col-sm-6">
                <div class="card border-0 shadow p-primary">
                    <div class="card-header bg-dark text-white border-0 p-4">
                        <h4 class="text-center text-capitalize mb-0">
                            Installation guide
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <h1 id="install">Install</h1>
                        <p>To install the product you will need a server with a Linux operating system, we recommend
                            using the centos distribution
                            and you need to install the following software:</p>
                        <ol>
                            <li>Nginx</li>
                            <li>php 8.1^ and php extension
                                cli,fpm,mysqlnd,pdo_mysql,zip,devel,gd,mbstring,curl,xml,pear,bcmath,json,pecl-redis5,exif,pcntl,sockets,gmp
                            </li>
                            <li>composer</li>
                            <li>redis</li>
                            <li>Database mysql percona</li>
                            <li>node 16^ and npm</li>
                            <li>global installed vite package for install <code>npm install -g vite</code></li>
                            <li>supervisor</li>
                        </ol>
                        <p>The merchant consists of two applications: a backend on laravel and a frontend on vue.js</p>
                        <p>Place the merchant backend in a location convenient for you, for example in the user
                            directory server /home/server/merchant.com
                            and place the frontend code in the frontend folder /home/server/merchant.com/frontend</p>
                        <p>edit nginx for you merchant api</p>
                        <pre><code><span class="hljs-section">server</span> {
    <span class="hljs-attribute">listen</span> <span class="hljs-number">80</span>;
    <span class="hljs-attribute">server_name</span> api.merchant.com;

    <span class="hljs-attribute">add_header</span> <span class="hljs-string">'Access-Control-Allow-Headers'</span> <span
                                        class="hljs-string">'*'</span> always;
    <span class="hljs-attribute">add_header</span> <span class="hljs-string">'Access-Control-Allow-Origin'</span> <span
                                        class="hljs-string">'*'</span> always;

    <span class="hljs-attribute">root</span>        /home/server/merchant.com/public;
    <span class="hljs-attribute">index</span>       index.php;

    <span class="hljs-attribute">location</span> / {
        <span class="hljs-attribute">try_files</span> <span class="hljs-variable">$uri</span> <span
                                        class="hljs-variable">$uri</span>/ /index.php?<span
                                        class="hljs-variable">$args</span>;
    }

    <span class="hljs-attribute">location</span> <span class="hljs-regexp">~ \.php$</span> {
        <span class="hljs-attribute">include</span> fastcgi.conf;
        <span class="hljs-attribute">fastcgi_pass</span> unix:/var/run/php-fpm.sock;
    }

    <span class="hljs-attribute">location</span> <span class="hljs-regexp">~ /\.(ht|svn|git)</span> {
            <span class="hljs-attribute">deny</span> all;
    }
}
</code></pre>
                        <p>and nginx config for control panel</p>
                        <pre><code><span class="hljs-section">server</span> {
    <span class="hljs-attribute">listen</span> <span class="hljs-number">80</span>;
    <span class="hljs-attribute">server_name</span> merchant.com;

    <span class="hljs-attribute">root</span>        /home/server/merchant.com/frontend/dist;
    <span class="hljs-attribute">index</span>       index.html;

    <span class="hljs-attribute">location</span> / {
            <span class="hljs-attribute">try_files</span> <span class="hljs-variable">$uri</span> <span
                                        class="hljs-variable">$uri</span>/ /index.html?<span
                                        class="hljs-variable">$args</span>;
    }

    <span class="hljs-attribute">location</span> <span class="hljs-regexp">~ /\.(ht|svn|git)</span> {
            <span class="hljs-attribute">deny</span> all;
    }

}
</code></pre>
                        <p>nginx config for checkout page</p>
                        <pre><code><span class="hljs-section">server</span> {
    <span class="hljs-attribute">listen</span> <span class="hljs-number">80</span>;
    <span class="hljs-attribute">server_name</span> pay.merchant.com;

    <span class="hljs-attribute">root</span>        /home/server/merchant.com/frontend/dist;
    <span class="hljs-attribute">index</span>       checkout.html;

    <span class="hljs-attribute">location</span> / {
            <span class="hljs-attribute">try_files</span> <span class="hljs-variable">$uri</span> <span
                                        class="hljs-variable">$uri</span>/ /checkout.html?<span class="hljs-variable">$args</span>;
    }

    <span class="hljs-attribute">location</span> <span class="hljs-regexp">~ /\.(ht|svn|git)</span> {
            <span class="hljs-attribute">deny</span> all;
    }

}
</code></pre>
                        <p>if you did everything correctly, you can proceed with the web installation</p>
                        <p>if you did everything correctly, you can proceed with the web installation</p>
                        <p>open page for installation <a
                                    href="http://merchant.com/install">http://merchant.com/install</a> where
                            merchant.com you domain</p>
                        <p><img src="./install_1.png" width="100%" alt="install"></p>
                        <p>the composer install button will install all the necessary dependencies for the backend
                            application</p>
                        <p>after installing the dependencies you should redirect to the page <a
                                    href="http://merchant.com/setup">http://merchant.com/setup</a></p>
                        <p><img src="./install_2.png" width="100%" alt="install"></p>
                        <p>on this page you enter all your data from the database
                            redis and specify the processing url</p>
                        <p>at this stage, all access to the .env file will be established, as well as migrations to the
                            database will be completed and your merchant will be registered in processing</p>
                        <p>if everything went well you should be redirected to the last page</p>
                        <p><img src="./install_3.png" width="100%" alt="install"></p>
                        <p>On this page you need to enter your details from the merchant’s personal account</p>
                        <p>At this step, a user with all privileges will be registered and the frontend part of the
                            application will be assembled</p>
                        <p>after successful installation you can start using the merchant</p>
                        <p>example config for supervisor
                            how start supervisor you can read on laravel doc
                            <a href="https://laravel.com/docs/10.x/queues#supervisor-configuration">https://laravel.com/docs/10.x/queues#supervisor-configuration</a>
                        </p>
                        <pre><code><span class="hljs-section">[program:laravel-worker]</span>
<span class="hljs-attr">command</span>=php /home/server/backend/www/artisan queue:work --queue=default,transfer,notifications,monitor
<span class="hljs-attr">process_name</span>=%(program_name)s_%(process_num)<span class="hljs-number">02</span>d
<span class="hljs-attr">numprocs</span>=<span class="hljs-number">8</span>
<span class="hljs-attr">priority</span>=<span class="hljs-number">999</span>
<span class="hljs-attr">autostart</span>=<span class="hljs-literal">true</span>
<span class="hljs-attr">autorestart</span>=<span class="hljs-literal">true</span>
<span class="hljs-attr">startsecs</span>=<span class="hljs-number">1</span>
<span class="hljs-attr">startretries</span>=<span class="hljs-number">3</span>
<span class="hljs-attr">user</span>=server
<span class="hljs-attr">redirect_stderr</span>=<span class="hljs-literal">true</span>
<span class="hljs-attr">stdout_logfile</span>=/home/server/backend/www/storage/logs/supervisord.log
</code></pre>

                        <button id="check" class="btn-block btn btn-dark btn-lg text-center">Check Requirements</button>
                    </div>

                </div>

            </div>
        </div>

    </div>

</div>
<div id="app" style="display: none;">
    <div class="p-3">
        <div class="row justify-content-center">
            <div class="col-sm-6">
                <div class="card border-0 shadow p-primary">
                    <div class="card-header bg-dark text-white border-0 p-4">
                        <h4 class="text-center text-capitalize mb-0">
                            Required Environments
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-capitalize py-2">
                            PHP
                            <span class="font-size-90">
                                Version <?php echo $result['php']['minimum'] ?> required
                            </span>
                            <div class="float-right">
                                <?php
                                $class = $result['php']['supported'] ? 'text-success' : 'text-danger'
                                ?>
                                <span class="<?php echo $class ?>">
                                    <?php echo $result['php']['current'] ?>
                                </span>
                            </div>
                        </div>
                        <div>
                            <h3>Php extension</h3>
                            <?php
                            $phpRequirements = $result['requirements']['php']; ?>
                            <ul class="list-group">
                                <?php
                                foreach ($phpRequirements as $key => $requirement) {
                                    ?>
                                    <li class="border-0 list-group-item d-flex justify-content-between align-items-center px-10 list-group-item-action ">
                                        <span class="param__prop"><?php echo ucfirst($key) ?></span>
                                        <?php if (!$requirement) { ?>
                                            <span class="text-danger param__value"><i
                                                        class="fa-solid fa-circle-xmark"></i></span>
                                        <?php } else { ?>
                                            <span class="text-success param__value"><i
                                                        class="fa-solid fa-circle-check"></i></span>

                                        <?php } ?>
                                    </li>
                                <?php } ?>
                                <li class="border-0 list-group-item d-flex justify-content-between align-items-center px-10 list-group-item-action ">
                                    <span class="param__prop">Exec is enabled </span>
                                    <?php if (!function_exists('exec')) { ?>
                                        <span class="text-danger param__value"><i
                                                    class="fa-solid fa-circle-xmark"></i></span>
                                    <?php } else { ?>
                                        <span class="text-success param__value"><i
                                                    class="fa-solid fa-circle-check"></i></span>
                                    <?php } ?>

                                </li>
                            </ul>
                            <br/>
                            <h3>Permission file and folder</h3>
                            <?php
                            $permissionsRequired = $result['permissions']['permissions'];

                            $permissions = array_filter($permissionsRequired, function ($permission) {
                                return !$permission['isSet'];
                            });

                            $permissions = array_map(function ($permission) {
                                return $permission['folder'];
                            }, $permissions);

                            $string = implode(', ', $permissions);
                            ?>
                            <ul class="list-group">
                                <?php
                                foreach ($permissionsRequired as $key => $requirement) {
                                    ?>
                                    <li class="border-0 list-group-item d-flex justify-content-between align-items-center px-10 list-group-item-action ">
                                        <span class="param__prop"><?php echo $requirement['folder'] ?></span>
                                        <?php if ($requirement['permission'] != $requirement['setPermission']) { ?>
                                            <span class="text-danger param__value">
                                                Need set permission <?php echo $requirement['permission']; ?>
                                                <i class="fa-solid fa-circle-xmark"></i></span>
                                        <?php } else { ?>
                                            <span class="text-success param__value"><i
                                                        class="fa-solid fa-circle-check"></i></span>
                                        <?php } ?>
                                    </li>
                                <?php } ?>
                            </ul>
                            <?php if (count($permissions)) { ?>
                                <div class="note-title d-flex">
                                    <i class="fa fa-book-open"></i>
                                    <h6 class="card-title pl-2">Attention </h6>
                                </div>

                                <div class="note note-warning p-4">
                                    <p class="m-1 text-muted"><b><?php echo $string ?></b> from <b>root</b> directory
                                        required server write permission to install and run the apps. You can remove
                                        write permission of <b>.env</b> after installation.</p>
                                </div>
                            <?php } ?>
                            <?php ?>
                        </div>
                        <form action="/install/" method="post">
                            <input type="hidden" value="composer" name="composer">
                            <?php if ($is_supported) { ?>
                                <button id="startInstall" class="btn-block btn btn-dark btn-lg text-center">Install
                                </button>
                            <?php } else { ?>
                                <button disabled class="btn-block btn btn-dark btn-lg text-center">Install</button>
                            <?php } ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById('check').addEventListener("click", function () {
        document.getElementById('app').style.display = "block"
        document.getElementById('welcome').style.display = "none"
    })
    document.getElementById('startInstall').addEventListener("click", function () {
        document.getElementById('app').style.display = "none"
        document.getElementById('progress').style.display = "block"
    })
</script>
</body>
</html>
