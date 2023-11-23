<?php
include 'Requirement.php';
include 'PermissionHelper.php';
function getResult(): array
{
    $config = json_decode(file_get_contents(__DIR__ . '/config.json'));

    $requirement = new Requirement($config);

    $requirementResult = $requirement->check();

    $permissions = new PermissionHelper($config);

    $permissionResult = $permissions->check();
    return array_merge(array_merge(array_merge([
        'php' => $requirement->checkPhpVersion(),
    ], $requirementResult), [
        'permissions' => $permissionResult
    ]), [
        'is_supported' => $requirement->isSupported() && $permissions->isSupported()
    ]);
}
