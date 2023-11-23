<?php
include 'PathHelper.php';

class PermissionHelper
{
    protected array $results = [];

    protected stdClass $config;

    protected PathHelper $path;

    public function __construct(stdClass $config)
    {
        $this->results['permissions'] = [];
        $this->results['errors'] = null;
        $this->config = $config;
        $this->path = PathHelper::new();
    }

    public function check(array $permissions = []): array
    {
        $permissions = count($permissions) ? $permissions : $this->config->permissions;
        $permissions = (array)$permissions;
        foreach ($permissions as $folder => $permission) {
            if (!($this->getPermission($folder))) {
                $this->addFileAndSetErrors($folder, $permission, false);
            } else {
                $this->addFile($folder, $permission, true);
            }
        }

        return $this->results;
    }

    function getPermissions($fileOrDir)
    {
        if (file_exists($this->path->getBasepath() . $fileOrDir)) {
            $permissions = fileperms($this->path->getBasepath() . $fileOrDir);
            $permissions = sprintf('%o', $permissions & 0777);
            return $permissions;
        } else {
            return false; // Файл или директория не существует
        }
    }


    private function getPermission($folder): bool
    {
        if (is_dir($this->path->getBasepath() . $folder)) {
            return $this->createTestFile($folder);
        } else {
            return $this->testFile($folder);
        }
    }

    private function createTestFile($folder): bool
    {
        try {
            $file = fopen($this->path->getBasepath() . $folder . 'test.txt', 'w');
            fwrite($file, "John Doe\n");
            fclose($file);
            $exist = file_exists($this->path->getBasepath() . $folder . 'test.txt');
            $this->deleteTestFile($this->path->getBasepath() . $folder . 'test.txt');
            return $exist;
        } catch (\Exception $exception) {
            return false;
        }
    }

    private function deleteTestFile($file_path): void
    {
        unlink($file_path);
    }

    private function testFile($file_path): bool
    {
        try {
            $fp = fopen($this->path->getBasepath() . $file_path, 'a');
            fwrite($fp, 'TEST=TEST');
            fclose($fp);
            file_put_contents($this->path->getBasepath() . $file_path, str_replace(
                'TEST=TEST', '', file_get_contents($this->path->getBasepath() . $file_path)
            ));
            return file_exists($this->path->getBasepath() . $file_path);
        } catch (\Exception $exception) {
            return false;
        }
    }

    private function addFile($folder, $permission, $isSet): void
    {
        $this->results['permissions'][] = [
            'folder'     => $folder,
            'permission' => $permission,
            'isSet'      => $isSet,
            'setPermission' => $this->getPermissions($folder)
        ];
    }

    private function addFileAndSetErrors($folder, $permission, $isSet): void
    {
        $this->addFile($folder, $permission, $isSet);

        $this->results['errors'] = true;
    }

    public function isSupported(): bool
    {
        $this->check();

        return !$this->results['errors'];
    }

}
