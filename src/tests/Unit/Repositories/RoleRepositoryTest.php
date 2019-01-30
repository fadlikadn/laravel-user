<?php
/**
 * Created by PhpStorm.
 * Users: dyangalih
 * Date: 05/11/18
 * Time: 00.41
 */

namespace WebAppId\User\Tests\Unit\Repositories;


use WebAppId\User\Repositories\RoleRepository;
use WebAppId\User\Services\Params\RoleParam;
use WebAppId\User\Tests\TestCase;

class RoleRepositoryTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
    }
    
    public function roleRepository()
    {
        return $this->getContainer()->make(RoleRepository::class);
    }
    
    public function getDummy()
    {
        $faker = $this->getFaker();
        $objRole = new RoleParam();
        $objRole->setName($faker->name);
        $objRole->setDescription($faker->text(190));
        return $objRole;
    }
    
    public function createDummy($dummy)
    {
        return $this->getContainer()->call([$this->roleRepository(), 'addRole'], ['request' => $dummy]);
    }
    
    public function testAddRole()
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
    }
    
    public function testGetAllRole()
    {
        $result = $this->getContainer()->call([$this->roleRepository(), 'getAllRole']);
        if (count($result) > 0) {
            self::assertTrue(true);
        } else {
            self::assertTrue(false);
        }
    }
}