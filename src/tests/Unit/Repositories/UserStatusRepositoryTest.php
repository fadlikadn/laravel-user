<?php
/**
 * Created by PhpStorm.
 * Users: dyangalih
 * Date: 03/11/18
 * Time: 18.03
 */

namespace Tests\Unit\Repositories;

use WebAppId\User\Repositories\UserStatusRepository;
use WebAppId\User\Tests\TestCase;

class UserStatusRepositoryTest extends TestCase
{
    
    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
    }
    
    protected function userRepository()
    {
        return $this->getContainer()->make(UserStatusRepository::class);
    }
    
    public function getUserStatusDummy()
    {
        $objUserStatus = new \StdClass();
        $objUserStatus->name = $this->getFaker()->name;
        return $objUserStatus;
    }
    
    public function createDummy(){
        return $this->getContainer()->call([$this->userRepository(), 'addUserStatus'], ['request' => $this->getUserStatusDummy()]);
    }
    
    public function testAddUserStatus()
    {
        $result = $this->createDummy();
        if($result!=null){
            $this->assertTrue(true);
        }else{
            $this->assertTrue(false);
        }
    }
    
    public function testGetAllUserStatus(){
        $dummy = $this->getUserStatusDummy();
        $result = $this->getContainer()->call([$this->userRepository(), 'addUserStatus'], ['request' => $dummy]);
        if($result!=null) {
            $result = $this->getContainer()->call([$this->userRepository(), 'getAll']);
            
            if(count($result)==0){
                $this->assertTrue(false);
            }else{
                $this->assertEquals($dummy->name, $result[count($result)-1]->name);
            }
        }else{
            $this->assertTrue(false);
        }
    }
    
    public function testGetUserStatusByName(){
        $dummy = $this->getUserStatusDummy();
        $result = $this->getContainer()->call([$this->userRepository(), 'addUserStatus'], ['request' => $dummy]);
        if($result!=null) {
            $result = $this->getContainer()->call([$this->userRepository(), 'getByName'],['name' => $dummy->name]);
            if($result == null){
                $this->assertTrue(false);
            }else{
                $this->assertTrue(true);
            }
        }
    }
    
}