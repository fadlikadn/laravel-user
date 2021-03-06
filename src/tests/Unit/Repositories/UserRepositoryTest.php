<?php
/**
 * Created by PhpStorm.
 * Users: dyangalih
 * Date: 03/11/18
 * Time: 16.02
 */

namespace WebAppId\User\Tests\Unit\Repositories;

use WebAppId\User\Models\Activation;
use WebAppId\User\Models\User;
use WebAppId\User\Repositories\ActivationRepository;
use WebAppId\User\Repositories\RoleRepository;
use WebAppId\User\Repositories\UserRepository;
use WebAppId\User\Repositories\UserStatusRepository;
use WebAppId\User\Repositories\UserRoleRepository;
use WebAppId\User\Services\Params\UserParam;
use WebAppId\User\Services\Params\UserRoleParam;
use WebAppId\User\Services\Params\UserSearchParam;
use WebAppId\User\Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    
    public function userRepository(): UserRepository
    {
        return $this->getContainer()->make(UserRepository::class);
    }
    
    private function userStatusRepository(): UserStatusRepository
    {
        return $this->getContainer()->make(UserStatusRepository::class);
    }
    
    private function activationRepository(): ActivationRepository
    {
        return $this->getContainer()->make(ActivationRepository::class);
    }
    
    private function userRoleRepository(): UserRoleRepository
    {
        return $this->getContainer()->make(UserRoleRepository::class);
    }
    
    private function roleRepository(): RoleRepository
    {
        return $this->getContainer()->make(RoleRepository::class);
    }
    
    public function getDummyUser(): ?UserParam
    {
        $objUser = new UserParam();
        
        $objUser->setName($this->getFaker()->name);
        $objUser->setEmail($this->getFaker()->safeEmail);
        $objUser->setStatusId(1);
        $objUser->setPassword($this->getFaker()->password);
        return $objUser;
    }
    
    public function createDummy($dummy): ?User
    {
        return $this->getContainer()->call([$this->userRepository(), 'addUser'], ['request' => $dummy]);
    }
    
    public function setActivation(int $userId): ?Activation
    {
        return $this->getContainer()->call([$this->activationRepository(), 'addActivation'], ['userId' => $userId]);
    }
    
    public function testAddUser(): ?User
    {
        $dummy = $this->getDummyUser();
        $result = $this->createDummy($dummy);
        $resultFailed = $this->createDummy($dummy);
        self::assertEquals(null, $resultFailed);
        
        $resultStatus = $this->getContainer()->call([$this->userStatusRepository(), 'getStatusById'], ['id' => $dummy->getStatusId()]);
        
        if ($result != null) {
            
            $objUserRole = new UserRoleParam();
            $objUserRole->setUserId($result->id);
            $objUserRole->setRoleId($this->getFaker()->numberBetween(1, 2));
            
            $resultUserRole = $this->getContainer()->call([$this->userRoleRepository(), 'addUserRole'], ['request' => $objUserRole]);
            
            if ($resultUserRole == null) {
                self::assertTrue(false);
            } else {
                self::assertTrue(true);
                self::assertEquals($objUserRole->getUserId(), $resultUserRole->user_id);
                self::assertEquals($objUserRole->getRoleId(), $resultUserRole->role_id);
                
                $roleResult = $this->getContainer()->call([$this->roleRepository(), 'getRoleById'], ['id' => $objUserRole->getRoleId()]);
                self::assertNotEquals(null, $roleResult);
                self::assertEquals($result->roles[0]->name, $roleResult->name);
            }
            
            $activationResult = $this->setActivation($result->id);
            self::assertNotEquals(null, $activationResult);
            
            $this->assertTrue(true);
            $this->assertEquals($dummy->getStatusId(), $result->status_id);
            $this->assertEquals($resultStatus->name, $result->status->name);
            
            return $result;
        } else {
            $this->assertTrue(false);
            return null;
        }
    }
    
    public function testGetUserByEmail(): void
    {
        $result = $this->createDummy($this->getDummyUser());
        
        if ($result != null) {
            $result = $this->getContainer()->call([$this->userRepository(), 'getUserByEmail'], ['email' => $result->email]);
            self::assertNotEquals(null, $result);
        }
    }
    
    public function testUpdateUserPassword(): void
    {
        $result = $this->testAddUser();
        if ($result != null) {
            $result = $this->getContainer()->call([$this->userRepository(), 'getUserByEmail'], ['email' => $result->email]);
            if ($result != null) {
                $result->password = $this->getFaker()->password;
                $resultUpdate = $this->getContainer()->call([$this->userRepository(), 'setUpdatePassword'], ['email' => $result->email, 'password' => $result->password]);
                self::assertNotEquals(null, $resultUpdate);
            } else {
                $this->assertTrue(false);
            }
        }
    }
    
    public function testUpdateUserStatus(): void
    {
        $result = $this->testAddUser();
        if ($result != null) {
            $result = $this->getContainer()->call([$this->userRepository(), 'getUserByEmail'], ['email' => $result->email]);
            if ($result != null) {
                $result->status_id = $this->getFaker()->numberBetween(1, 4);
                $resultFailed = $this->getContainer()->call([$this->userRepository(), 'setUpdateStatusUser'], ['email' => $this->getFaker()->safeEmail, 'status' => $result->status_id]);
                self::assertEquals(null, $resultFailed);
                $resultUpdate = $this->getContainer()->call([$this->userRepository(), 'setUpdateStatusUser'], ['email' => $result->email, 'status' => $result->status_id]);
                self::assertNotEquals(null, $resultUpdate);
            } else {
                $this->assertTrue(false);
            }
        }
    }
    
    public function testActivationUser(): void
    {
        $result = $this->testAddUser();
        if ($result != null) {
            self::assertTrue(true);
            $resultActivate = $this->getContainer()->call([$this->activationRepository(), 'setActivate'], ['key' => $result->activation->key]);
            if ($resultActivate == null) {
                self::assertTrue(false);
            } else {
                self::assertTrue(true);
                $resultActivate = $this->getContainer()->call([$this->activationRepository(), 'getActivationByKey'], ['key' => $result->activation->key]);
                self::assertEquals($resultActivate->status, 'used');
                self::assertEquals($resultActivate->isValid, 'valid');
            }
        }
        
    }
    
    public function testInvalidKey(): void
    {
        $result = $this->testAddUser();
        
        if ($result != null) {
            $resultActivate = $this->getContainer()->call([$this->activationRepository(), 'setActivate'], ['key' => 'invalid key']);
            self::assertNotEquals('null', $resultActivate);
        }
    }
    
    public function testActiveAlreadyUsed(): void
    {
        $result = $this->testAddUser();
        if ($result != null) {
            self::assertTrue(true);
            $resultActivate = $this->getContainer()->call([$this->activationRepository(), 'setActivate'], ['key' => $result->activation->key]);
            if ($resultActivate == null) {
                self::assertTrue(false);
            } else {
                self::assertTrue(true);
                $resultActivate = $this->getContainer()->call([$this->activationRepository(), 'setActivate'], ['key' => $result->activation->key]);
                self::assertEquals($resultActivate->status, 'already used');
            }
        }
        
    }
    
    public function testUserCountAll(): void
    {
        $randomNumber = $this->getFaker()->numberBetween(1, 20);
        for ($i = 0; $i < $randomNumber; $i++) {
            $this->testAddUser();
        }
        
        $count = $this->getContainer()->call([$this->userRepository(), 'getCountAllUser']);
        
        $this->assertEquals($randomNumber + 1, $count);
    }
    
    public function testUserSearchCount(): void
    {
        $randomNumber = $this->getFaker()->numberBetween(5, 20);
        
        $picNumber = $this->getFaker()->numberBetween(0, $randomNumber);
        
        for ($i = 0; $i < $randomNumber; $i++) {
            if ($picNumber != $i) {
                $this->testAddUser();
            } else {
                $userData = $this->testAddUser();
            }
        }
        
        $request = new UserSearchParam();
        $request->setQ($userData->name);
        
        $count = $this->getContainer()->call([$this->userRepository(), 'getUserSearchCount'], ['userSearchParam' => $request]);
        $this->assertEquals(1, $count);
        
        $request->setQ($this->getFaker()->password());
        $count = $this->getContainer()->call([$this->userRepository(), 'getUserSearchCount'], ['userSearchParam' => $request]);
        $this->assertEquals(0, $count);
    }
    
    public function testUserSearchPaging(): void
    {
        $paging = 12;
        
        $randomNumber = $this->getFaker()->numberBetween($paging, 20);
        
        $result = [];
        
        for ($i = 0; $i < $randomNumber; $i++) {
            $result[] = $this->testAddUser();
        }
        
        $request = new UserSearchParam();
        $request->setQ('');
        
        $resultSearch = $this->getContainer()->call([$this->userRepository(), 'getUserSearch'], ['userSearchParam' => $request, 'paginate' => $paging]);
        $this->assertEquals($paging, count($resultSearch));
        
        $request->setQ($this->getFaker()->password());
        $count = $this->getContainer()->call([$this->userRepository(), 'getUserSearchCount'], ['userSearchParam' => $request]);
        $this->assertEquals(0, $count);
    }
    
    public function testUpdateUserName(): void
    {
        $result = $this->testAddUser();
        
        $newName = $this->getFaker()->name;
        
        $resultNew = $this->getContainer()->call([$this->userRepository(), 'setUpdateName'], ['name' => $newName, 'email' => $result->email]);
        
        $this->assertNotEquals($result->name, $resultNew->name);
    }
    
    public function testDeleteUserByEmail()
    {
        $randomNumber = $this->getFaker()->numberBetween(5, 20);
        
        $result = [];
        
        for ($i = 0; $i < $randomNumber; $i++) {
            $result[] = $this->testAddUser();
        }
        
        $picNumber = $this->getFaker()->numberBetween(0, $randomNumber);
        
        $deleteResult = $this->getContainer()->call([$this->userRepository(), 'deleteUserByEmail'], ['email' => $result[$picNumber]->email]);
        
        self::assertEquals(true, $deleteResult);
        
        $resultUserData = $this->getContainer()->call([$this->userRepository(), 'getUserByEmail'], ['email' => $result[$picNumber]->email]);
        
        self::assertEquals(null, $resultUserData);
    }
    
    public function testDeleteRole(): void
    {
        $user = $this->testAddUser();
        $status = $this->getContainer()->call([$this->userRoleRepository(), 'deleteUserRoleByUserId'], ['userId' => $user->id]);
        if ($status) {
            self::assertTrue(true);
        } else {
            self::assertTrue(false);
        }
    }
}