<?php
/**
 * Created by PhpStorm.
 * UserParam: dyangalih
 * Date: 2019-01-26
 * Time: 13:20
 */

namespace Tests\Feature\Services;


use Illuminate\Contracts\Container\BindingResolutionException;
use WebAppId\User\Services\UserStatusService;
use WebAppId\User\Tests\TestCase;
use WebAppId\User\Tests\Unit\Repositories\UserStatusRepositoryTest;

class UserStatusServiceTest extends TestCase
{

    /**
     * @return UserStatusService|mixed
     * @throws BindingResolutionException
     */
    private function userStatusService(){
        return $this->getContainer()->make(UserStatusService::class);
    }
    
    /**
     * @return mixed
     */
    private function getUserStatusDummy(){
        $userStatusRepositoryTest = new UserStatusRepositoryTest();
        $userStatusRepositoryTest->setUp();
        return $userStatusRepositoryTest->createDummy();
    }
    
    public function testGetAll()
    {
        $userStatusDummy = $this->getUserStatusDummy();
        if($userStatusDummy!=null) {
            $result = $this->getContainer()->call([$this->userStatusService(), 'getAll']);
            if($result==null){
                self::assertTrue(false);
            }else{
                self::assertEquals($result[count($result)-1]->name, $userStatusDummy->name);
            }
            
        }else{
            self::assertTrue(false);
        }
    }
    
}