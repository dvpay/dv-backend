<?php
declare(strict_types=1);

namespace App\Setup\Helper;
/**
 *
 */
class PermissionsHelper
{
    /**
     * @var array
     */
    protected $results = [];

    /**
     *
     */
    public function __construct()
    {
        $this->results['permissions'] = [];
        $this->results['errors'] = null;
    }


    /**
     * @param array $permissions
     * @return array
     */
    public function check(array $permissions = [])
    {
        $permissions = count($permissions) ? $permissions : config('installer.permissions');
        foreach ($permissions as $folder => $permission) {
            if (!($this->getPermission($folder) >= $permission)) {
                $this->addFileAndSetErrors($folder, $permission, false);
            } else {
                $this->addFile($folder, $permission, true);
            }
        }

        return $this->results;
    }

    /**
     * @param $folder
     * @return bool
     */
    public function createTestFile($folder): bool
    {
        try {
            $file = fopen(base_path($folder . 'test.txt'), 'w');
            fwrite($file, "John Doe\n");
            fclose($file);
            $this->deleteTestFile(base_path($folder . 'test.txt'));
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param $file_path
     * @return void
     */
    public function deleteTestFile($file_path): void
    {
        unlink($file_path);
    }

    /**
     * @param $file_path
     * @return bool
     */
    public function testFile($file_path): bool
    {
        try {
            $fp = fopen(base_path($file_path), 'a');
            fwrite($fp, 'TEST=TEST');
            fclose($fp);
            file_put_contents(base_path($file_path), str_replace(
                'TEST=TEST', '', file_get_contents(base_path($file_path))
            ));
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param $folder
     * @return bool
     */
    private function getPermission($folder): bool
    {
        if (is_dir(base_path($folder))) {
            return $this->createTestFile($folder);
        } else {
            return $this->testFile($folder);
        }
    }


    /**
     * @param $folder
     * @param $permission
     * @param $isSet
     * @return void
     */
    private function addFile($folder, $permission, $isSet): void
    {
        $this->results['permissions'][] = [
            'folder'     => $folder,
            'permission' => $permission,
            'isSet'      => $isSet,
        ];
    }

    /**
     * @param $folder
     * @param $permission
     * @param $isSet
     * @return void
     */
    private function addFileAndSetErrors($folder, $permission, $isSet): void
    {
        $this->addFile($folder, $permission, $isSet);

        $this->results['errors'] = true;
    }

    /**
     * @return bool
     */
    public function isSupported(): bool
    {
        $this->check();

        return !$this->results['errors'];
    }
}
