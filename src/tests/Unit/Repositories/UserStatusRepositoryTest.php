<?php
/**
 * Created by PhpStorm.
 * Users: dyangalih
 * Date: 03/11/18
 * Time: 18.03
 */

namespace WebAppId\User\Tests\Unit\Repositories;

use WebAppId\User\Models\UserStatus;
use WebAppId\User\Repositories\UserStatusRepository;
use WebAppId\User\Tests\TestCase;

class UserStatusRepositoryTest extends TestCase
{
    
    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
    }
    
    protected function userStatusRepository(): UserStatusRepository
    {
        return $this->getContainer()->make(UserStatusRepository::class);
    }
    
    public function getUserStatusDummy()
    {
        $objUserStatus = new \StdClass();
        $objUserStatus->name = $this->getFaker()->name;
        return $objUserStatus;
    }
    
    public function createDummy(): UserStatus
    {
        return $this->getContainer()->call([$this->userStatusRepository(), 'addUserStatus'], ['request' => $this->getUserStatusDummy()]);
    }
    
    public function testAddUserStatus()
    {
        $result = $this->createDummy();
        self::assertNotEquals(null, $result);
    }
    
    public function testGetAllUserStatus(): void
    {
        $dummy = $this->getUserStatusDummy();
        $result = $this->getContainer()->call([$this->userStatusRepository(), 'addUserStatus'], ['request' => $dummy]);
        if ($result != null) {
            $result = $this->getContainer()->call([$this->userStatusRepository(), 'getAll']);
            
            if (count($result) == 0) {
                $this->assertTrue(false);
            } else {
                $this->assertEquals($dummy->name, $result[count($result) - 1]->name);
            }
        } else {
            $this->assertTrue(false);
        }
    }
    
    public function testGetUserStatusByName(): void
    {
        $dummy = $this->getUserStatusDummy();
        $result = $this->getContainer()->call([$this->userStatusRepository(), 'addUserStatus'], ['request' => $dummy]);
        if ($result != null) {
            $result = $this->getContainer()->call([$this->userStatusRepository(), 'getByName'], ['name' => $dummy->name]);
            self::assertNotEquals(null, $result);
        }
    }
    
    public function testUserStatusById(): void
    {
        $dummy = $this->getUserStatusDummy();
        $result = $this->getContainer()->call([$this->userStatusRepository(), 'addUserStatus'], ['request' => $dummy]);
        if ($result != null) {
            $result = $this->getContainer()->call([$this->userStatusRepository(), 'getStatusById'], ['id' => $result->id]);
            self::assertNotEquals(null, $result);
        }
    }
    
}