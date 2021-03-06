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
use WebAppId\User\Services\Params\UserStatusParam;
use WebAppId\User\Tests\TestCase;

class UserStatusRepositoryTest extends TestCase
{
    protected function userStatusRepository(): UserStatusRepository
    {
        return $this->getContainer()->make(UserStatusRepository::class);
    }
    
    public function getUserStatusDummy(): UserStatusParam
    {
        $objUserStatus = new UserStatusParam();
        $objUserStatus->setName($this->getFaker()->name);
        return $objUserStatus;
    }
    
    public function createDummy(): UserStatus
    {
        return $this->getContainer()->call([$this->userStatusRepository(), 'addUserStatus'], ['userStatusParam' => $this->getUserStatusDummy()]);
    }
    
    public function testAddUserStatus()
    {
        $result = $this->createDummy();
        self::assertNotEquals(null, $result);
    }
    
    public function testGetAllUserStatus(): void
    {
        $dummy = $this->getUserStatusDummy();
        $result = $this->getContainer()->call([$this->userStatusRepository(), 'addUserStatus'], ['userStatusParam' => $dummy]);
        
        if ($result != null) {
            $result = $this->getContainer()->call([$this->userStatusRepository(), 'getAll']);
            
            if (count($result) == 0) {
                $this->assertTrue(false);
            } else {
                $this->assertEquals($dummy->getName(), $result[count($result) - 1]->name);
            }
        } else {
            $this->assertTrue(false);
        }
    }
    
    public function testGetUserStatusByName(): void
    {
        $dummy = $this->getUserStatusDummy();
        $result = $this->getContainer()->call([$this->userStatusRepository(), 'addUserStatus'], ['userStatusParam' => $dummy]);
        if ($result != null) {
            $result = $this->getContainer()->call([$this->userStatusRepository(), 'getByName'], ['name' => $dummy->getName()]);
            self::assertNotEquals(null, $result);
        }
    }
    
    public function testUserStatusById(): void
    {
        $dummy = $this->getUserStatusDummy();
        $result = $this->getContainer()->call([$this->userStatusRepository(), 'addUserStatus'], ['userStatusParam' => $dummy]);
        if ($result != null) {
            $result = $this->getContainer()->call([$this->userStatusRepository(), 'getStatusById'], ['id' => $result->id]);
            self::assertNotEquals(null, $result);
        }
    }
    
}