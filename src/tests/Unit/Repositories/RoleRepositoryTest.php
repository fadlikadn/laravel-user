<?php
/**
 * Created by PhpStorm.
 * Users: dyangalih
 * Date: 05/11/18
 * Time: 00.41
 */

namespace WebAppId\User\Tests\Unit\Repositories;


use WebAppId\User\Models\Role;
use WebAppId\User\Repositories\RoleRepository;
use WebAppId\User\Services\Params\RoleParam;
use WebAppId\User\Tests\TestCase;

class RoleRepositoryTest extends TestCase
{
    public function roleRepository(): RoleRepository
    {
        return $this->getContainer()->make(RoleRepository::class);
    }
    
    public function getDummy(): RoleParam
    {
        $faker = $this->getFaker();
        $objRole = new RoleParam();
        $objRole->setName($faker->name);
        $objRole->setDescription($faker->text(190));
        return $objRole;
    }
    
    public function createDummy($dummy): Role
    {
        return $this->getContainer()->call([$this->roleRepository(), 'addRole'], ['request' => $dummy]);
    }
    
    public function testAddRole(): Role
    {
        $dummy = $this->getDummy();
        $result = $this->createDummy($dummy);
        if ($result == null) {
            self::assertTrue(false);
        } else {
            self::assertTrue(true);
            self::assertEquals($dummy->getName(), $result->name);
            self::assertEquals($dummy->getDescription(), $result->description);
        }
        return $result;
    }
    
    public function testGetAllRole(): void
    {
        $result = $this->getContainer()->call([$this->roleRepository(), 'getAllRole']);
        
        if (count($result) > 0) {
            self::assertTrue(true);
        } else {
            self::assertTrue(false);
        }
    }
    
    public function testGetRoleByName(): void
    {
        $result = $this->testAddRole();
        
        $resultSearch = $this->getContainer()->call([$this->roleRepository(), 'getRoleByName'], ['name' => $result->name]);
        self::assertEquals($result->name, $resultSearch->name);
        self::assertEquals($result->description, $resultSearch->description);
    }
    
    public function testGetRoleById(): void
    {
        $result = $this->testAddRole();
        
        $roleResult = $this->getContainer()->call([$this->roleRepository(),'getRoleById'], ['id' => $result->id]);
        $this->assertNotEquals(null, $roleResult);
        self::assertEquals($result->name, $roleResult->name);
        self::assertEquals($result->description, $roleResult->description);
    }
}