<?php
/**
 * Created by PhpStorm.
 * User: dyangalih
 * Date: 2019-01-30
 * Time: 21:57
 */

namespace WebAppId\User\Tests\Feature\Services;


use Carbon\Carbon;
use WebAppId\User\Services\ActivationService;
use WebAppId\User\Services\UserService;
use WebAppId\User\Tests\TestCase;

class ActivationTest extends TestCase
{
    private $userServiceTest;
    private $userService;
    private $activationService;
    
    protected function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->userServiceTest()->setUp();
    }
    
    public function userService(): UserService
    {
        if ($this->userService == null) {
            $this->userService = $this->getContainer()->make(UserService::class);
        }
        
        return $this->userService;
    }
    
    public function activationService(): ActivationService
    {
        if ($this->activationService == null) {
            $this->activationService = $this->getContainer()->make(ActivationService::class);
        }
        
        return $this->activationService;
    }
    
    public function userServiceTest(): UserServiceTest
    {
        if ($this->userServiceTest == null) {
            $this->userServiceTest = new UserServiceTest();
        }
        return $this->userServiceTest;
    }
    
    public function testActivation(): void
    {
        $dummyUser = $this->userServiceTest()->testAddUser();
        $resultUser = $this->getContainer()->call([$this->userService(), 'getUserByEmail'], ['email' => $dummyUser->getEmail()]);
        $activationKey = $resultUser->getData()->activation->key;
    
        /**
         * test expired key
         */
        $resultUser->getData()->activation->valid_until = Carbon::now('UTC')->addHour(-8)->toDateTimeString();
        $resultUser->getData()->activation->save();
        $resultExpiredKey = $this->getContainer()->call([$this->activationService(),'activate'],['activationKey' => $activationKey]);
        self::assertEquals(false, $resultExpiredKey->getStatus());
    
        $resultUser->getData()->activation->valid_until = Carbon::now('UTC')->addHour(8)->toDateTimeString();
        $resultUser->getData()->activation->save();
    
        /**
         * test not valid uuid
         */
        $resultNotValid = $this->getContainer()->call([$this->activationService(),'activate'],['activationKey' => $this->getFaker()->uuid]);
        self::assertEquals(false, $resultNotValid->getStatus());
    
        /**
         *  test success activate user
         */
        $resultActivation = $this->getContainer()->call([$this->activationService(),'activate'],['activationKey' => $activationKey]);
        self::assertEquals(true, $resultActivation->getStatus());
    
        /**
         *  test double activation
         */
        $resultUsedKey = $this->getContainer()->call([$this->activationService(),'activate'],['activationKey' => $activationKey]);
        self::assertEquals(false, $resultUsedKey->getStatus());
    }
}