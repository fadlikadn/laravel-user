<?php
/**
 * Created by PhpStorm.
 * UserParam: dyangalih
 * Date: 2019-01-26
 * Time: 13:17
 */

namespace Tests\Feature\Services;


use WebAppId\User\Services\RoleService;
use WebAppId\User\Tests\TestCase;

class RoleServiceTest extends TestCase
{
    protected function roleService()
    {
        return $this->getContainer()->make(RoleService::class);
    }
    
    public function testGetAllRole()
    {
        $result = $this->getContainer()->call([$this->roleService(), 'getAllRole']);
        if ($result == null) {
            self::assertTrue(false);
        }else{
            self::assertTrue(true);
        }
    }
    
}