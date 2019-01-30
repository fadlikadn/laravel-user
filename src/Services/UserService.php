<?php

namespace WebAppId\User\Services;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;
use WebAppId\User\Repositories\UserRepository;
use WebAppId\User\Repositories\UserRoleRepository;
use WebAppId\User\Response\AddUserResponse;
use WebAppId\User\Response\ChangePasswordResponse;
use WebAppId\User\Services\Params\ChangePasswordParam;
use WebAppId\User\Services\Params\UserParam;
use WebAppId\User\Services\Params\UserRoleParam;

/**
 * Class UserService
 * @package App\Http\Services
 */
class UserService
{
    private $container;
    
    /**
     * UserService constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    /**
     * @param UserParam $request
     * @param UserRepository $userRepository
     * @param UserRoleRepository $userRoleRepository
     * @param UserRoleParam $userRoleParam
     * @param AddUserResponse $addUserResponse
     * @return AddUserResponse
     */
    public function addUser(UserParam $request, UserRepository $userRepository, UserRoleRepository $userRoleRepository, UserRoleParam $userRoleParam, AddUserResponse $addUserResponse): AddUserResponse
    {
        DB::beginTransaction();
        $resultUser = $this->container->call([$userRepository, 'addUser'], ['request' => $request]);
        if ($resultUser == null) {
            $addUserResponse->setStatus(false);
            $addUserResponse->setMessage('add user failed');
            DB::rollback();
            return $addUserResponse;
        } else {
            $userRoleParam->setUserId($resultUser->id);
            $userRoleParam->setRoleId($request->getRoleId());
            
            $resultUserRole = $this->container->call([$userRoleRepository, 'addUserRole'], ['request' => $userRoleParam]);
            if ($resultUserRole == null) {
                DB::rollback();
                $addUserResponse->setStatus(false);
                $addUserResponse->setMessage('add user role failed');
                return null;
            } else {
                $addUserResponse->setStatus(true);
                $addUserResponse->setUser($resultUser);
                $addUserResponse->setRoles($resultUser->roles);
                DB::commit();
                return $addUserResponse;
            }
        }
    }
    
    /**
     * @param ChangePasswordParam $changePasswordParam
     * @param UserRepository $userRepository
     * @param ChangePasswordResponse $changePasswordResponse
     * @return ChangePasswordResponse
     */
    public function changePassword(ChangePasswordParam $changePasswordParam, UserRepository $userRepository, ChangePasswordResponse $changePasswordResponse, $force = false): ChangePasswordResponse
    {
        
        $userResult = $this->container->call([$userRepository, 'getUserByEmail'], ['email' => $changePasswordParam->getEmail()]);
        if ($userResult != null) {
            if ($changePasswordParam->getPassword() !== $changePasswordParam->getRetypePassword() && !$force) {
                $changePasswordResponse->setStatus(false);
                $changePasswordResponse->setMessage("New password and retype password not match");
            } elseif (!password_verify($changePasswordParam->getOldPassword(), $userResult->password) && !$force) {
                $changePasswordResponse->setStatus(false);
                $changePasswordResponse->setMessage("Old password not match");
            } else {
                $changePasswordResult = $this->container->call([$userRepository, 'setUpdatePassword'], ['email' => $changePasswordParam->getEmail(), 'password' => $changePasswordParam->getPassword()]);
                if ($changePasswordResult == null) {
                    $changePasswordResponse->setStatus(false);
                    $changePasswordResponse->setMessage("Update Password Failed, please contact your admin");
                } else {
                    $changePasswordResponse->setStatus(true);
                    $changePasswordResponse->setMessage("Update Password Success");
                }
            }
        } else {
            $changePasswordResponse->setStatus(false);
            $changePasswordResponse->setMessage("User not found");
        }
        
        return $changePasswordResponse;
    }
    
    /**
     * @param ChangePasswordParam $changePasswordParam
     * @param UserRepository $userRepository
     * @param ChangePasswordResponse $changePasswordResponse
     * @return ChangePasswordResponse
     */
    public function changePasswordByAdmin(ChangePasswordParam $changePasswordParam, UserRepository $userRepository, ChangePasswordResponse $changePasswordResponse): ChangePasswordResponse
    {
        $changePasswordResult = $this->container->call([$userRepository, 'setUpdatePassword'], ['email' => $changePasswordParam->getEmail(), 'password' => $changePasswordParam->getPassword()]);
        if ($changePasswordResult == null) {
            $changePasswordResponse->setStatus(false);
            $changePasswordResponse->setMessage("Update Password Failed, please contact your admin");
        } else {
            $changePasswordResponse->setStatus(true);
            $changePasswordResponse->setMessage("Update Password Success");
        }
        
        return $changePasswordResponse;
    }
}