<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\User\UserHandlerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\User;

use eZ\Publish\API\Repository\Values\User\Role as APIRole;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\User;
use eZ\Publish\Core\Persistence\Legacy\User\Role\LimitationConverter;
use eZ\Publish\Core\Persistence\Legacy\User\Role\LimitationHandler\ObjectStateHandler as ObjectStateLimitationHandler;
use eZ\Publish\SPI\Persistence;

/**
 * Test case for UserHandlerTest.
 */
class UserHandlerTest extends TestCase
{
    protected function getUserHandler()
    {
        $dbHandler = $this->getDatabaseHandler();

        return new User\Handler(
            new User\Gateway\DoctrineDatabase($dbHandler),
            new User\Role\Gateway\DoctrineDatabase($dbHandler),
            new User\Mapper(),
            new LimitationConverter([new ObjectStateLimitationHandler($dbHandler)])
        );
    }

    protected function getValidUser()
    {
        $user = new Persistence\User();
        $user->id = 42;
        $user->login = 'kore';
        $user->email = 'kore@example.org';
        $user->passwordHash = '1234567890';
        $user->hashAlgorithm = 2;
        $user->isEnabled = true;
        $user->maxLogin = 23;

        return $user;
    }

    protected function getValidUserToken($time = null)
    {
        $userToken = new Persistence\User\UserTokenUpdateStruct();
        $userToken->userId = 42;
        $userToken->hashKey = md5('hash');
        $userToken->time = $time ?? (new \DateTime())->add(new \DateInterval('P1D'))->getTimestamp();

        return $userToken;
    }

    public function testCreateUser()
    {
        $handler = $this->getUserHandler();

        $handler->create($this->getValidUser());
        $this->assertQueryResult(
            [[1]],
            $this->handler->createSelectQuery()->select('COUNT( * )')->from('ezuser'),
            'Expected one user to be created.'
        );

        $this->assertQueryResult(
            [[1]],
            $this->handler->createSelectQuery()->select('COUNT( * )')->from('ezuser_setting'),
            'Expected one user setting to be created.'
        );
    }

    public function testCreateDuplicateUser()
    {
        $this->expectException(\Doctrine\DBAL\DBALException::class);

        $handler = $this->getUserHandler();

        $handler->create($user = $this->getValidUser());
        $handler->create($user);
    }

    public function testInsertIncompleteUser()
    {
        $this->expectException(\Doctrine\DBAL\DBALException::class);

        $handler = $this->getUserHandler();

        $user = new Persistence\User();
        $user->id = 42;

        $handler->create($user);
    }

    public function testLoadUser()
    {
        $handler = $this->getUserHandler();
        $handler->create($user = $this->getValidUser());

        $this->assertEquals(
            $user,
            $handler->load($user->id)
        );
    }

    public function testLoadUnknownUser()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\NotFoundException::class);

        $handler = $this->getUserHandler();

        $handler->load(1337);
    }

    public function testLoadUserByLogin()
    {
        $handler = $this->getUserHandler();
        $handler->create($user = $this->getValidUser());

        $loadedUser = $handler->loadByLogin($user->login);
        $this->assertEquals(
            $user,
            $loadedUser
        );
    }

    public function testLoadUserByEmailNotFound()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\NotFoundException::class);

        $handler = $this->getUserHandler();
        $handler->create($user = $this->getValidUser());

        $handler->loadByLogin($user->email);
    }

    public function testLoadUserByEmail()
    {
        $handler = $this->getUserHandler();
        $handler->create($user = $this->getValidUser());

        $users = $handler->loadByEmail($user->email);
        $this->assertEquals(
            $user,
            $users[0]
        );
    }

    public function testLoadUserByTokenNotFound()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\NotFoundException::class);

        $handler = $this->getUserHandler();
        $handler->create($user = $this->getValidUser());
        $handler->updateUserToken($this->getValidUserToken());

        $handler->loadUserByToken('asd');
    }

    public function testLoadUserByToken()
    {
        $handler = $this->getUserHandler();
        $handler->create($user = $this->getValidUser());
        $handler->updateUserToken($userToken = $this->getValidUserToken());

        $loadedUser = $handler->loadUserByToken($userToken->hashKey);
        $this->assertEquals(
            $user,
            $loadedUser
        );
    }

    public function testUpdateUserToken()
    {
        $handler = $this->getUserHandler();

        $handler->updateUserToken($userToken = $this->getValidUserToken(1234567890));

        $this->assertQueryResult(
            [['0800fc577294c34e0b28ad2839435945', 1, 1234567890, 42]],
            $this->handler->createSelectQuery()->select('*')->from('ezuser_accountkey'),
            'Expected user data to be updated.'
        );

        $handler->updateUserToken($userToken = $this->getValidUserToken(2234567890));

        $this->assertQueryResult(
            [['0800fc577294c34e0b28ad2839435945', 1, 2234567890, 42]],
            $this->handler->createSelectQuery()->select('*')->from('ezuser_accountkey'),
            'Expected user token data to be updated.'
        );
    }

    public function testExpireUserToken()
    {
        $handler = $this->getUserHandler();

        $handler->updateUserToken($userToken = $this->getValidUserToken(1234567890));

        $this->assertQueryResult(
            [['0800fc577294c34e0b28ad2839435945', 1, 1234567890, 42]],
            $this->handler->createSelectQuery()->select('*')->from('ezuser_accountkey'),
            'Expected user data to be updated.'
        );

        $handler->expireUserToken($userToken->hashKey);

        $this->assertQueryResult(
            [['0800fc577294c34e0b28ad2839435945', 1, 0, 42]],
            $this->handler->createSelectQuery()->select('*')->from('ezuser_accountkey'),
            'Expected user token to be expired.'
        );
    }

    public function testCreateAndDeleteUser()
    {
        $handler = $this->getUserHandler();

        $handler->create($user = $this->getValidUser());
        $this->assertQueryResult(
            [[1]],
            $this->handler->createSelectQuery()->select('COUNT( * )')->from('ezuser'),
            'Expected one user to be created.'
        );

        $this->assertQueryResult(
            [[1]],
            $this->handler->createSelectQuery()->select('COUNT( * )')->from('ezuser_setting'),
            'Expected one user setting to be created.'
        );

        $handler->delete($user->id);
        $this->assertQueryResult(
            [[0]],
            $this->handler->createSelectQuery()->select('COUNT( * )')->from('ezuser'),
            'Expected one user to be removed.'
        );

        $this->assertQueryResult(
            [[0]],
            $this->handler->createSelectQuery()->select('COUNT( * )')->from('ezuser_setting'),
            'Expected one user setting to be removed.'
        );
    }

    public function testDeleteNonExistingUser()
    {
        $handler = $this->getUserHandler();

        $handler->delete(1337);
        $this->assertQueryResult(
            [[0]],
            $this->handler->createSelectQuery()->select('COUNT( * )')->from('ezuser'),
            'Expected no existing user.'
        );
    }

    public function testUpdateUser()
    {
        $handler = $this->getUserHandler();

        $handler->create($user = $this->getValidUser());

        $user->login = 'New_lögin';
        $handler->update($user);

        $this->assertQueryResult(
            [[42, 'kore@example.org', 'New_lögin', 1234567890, '2']],
            $this->handler->createSelectQuery()->select('*')->from('ezuser'),
            'Expected user data to be updated.'
        );
    }

    public function testUpdateUserSettings()
    {
        $handler = $this->getUserHandler();

        $handler->create($user = $this->getValidUser());

        $user->maxLogin = 42;
        $handler->update($user);

        $this->assertQueryResult(
            [[1, 42, 42]],
            $this->handler->createSelectQuery()->select('*')->from('ezuser_setting'),
            'Expected user data to be updated.'
        );
    }

    public function testSilentlyUpdateNotExistingUser()
    {
        $handler = $this->getUserHandler();
        $handler->update($this->getValidUser());
        $this->assertQueryResult(
            [[0]],
            $this->handler->createSelectQuery()->select('COUNT( * )')->from('ezuser'),
            'Expected no existing user.'
        );
    }

    public function testCreateNewRoleWithoutPolicies()
    {
        $handler = $this->getUserHandler();

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';

        $handler->createRole($createStruct);

        $this->assertQueryResult(
            [[1, 'Test', -1]],
            $this->handler->createSelectQuery()->select('id', 'name', 'version')->from('ezrole'),
            'Expected a new role draft.'
        );
    }

    public function testCreateRoleDraftWithoutPolicies()
    {
        $handler = $this->getUserHandler();

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';

        $roleDraft = $handler->createRole($createStruct);
        $handler->publishRoleDraft($roleDraft->id);

        $handler->createRoleDraft($roleDraft->id);

        $publishedRoleId = 1;
        $this->assertQueryResult(
            [
                [$publishedRoleId, 'Test', APIRole::STATUS_DEFINED],
                [2, 'Test', $publishedRoleId],
            ],
            $this->handler->createSelectQuery()->select('id', 'name', 'version')->from('ezrole'),
            'Expected a role and a role draft.'
        );
    }

    public function testCreateNewRoleRoleId()
    {
        $handler = $this->getUserHandler();

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';

        $roleDraft = $handler->createRole($createStruct);

        $this->assertSame('1', $roleDraft->id);
    }

    public function testLoadRole()
    {
        $handler = $this->getUserHandler();

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';

        $roleDraft = $handler->createRole($createStruct);
        $handler->publishRoleDraft($roleDraft->id);
        $role = $handler->loadRole($roleDraft->id);

        $this->assertEquals(
            $roleDraft->id,
            $role->id
        );
    }

    public function testLoadRoleWithPolicies()
    {
        $handler = $this->getUserHandler();

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';

        $roleDraft = $handler->createRole($createStruct);

        $policy = new Persistence\User\Policy();
        $policy->module = 'foo';
        $policy->function = 'bar';

        $handler->addPolicyByRoleDraft($roleDraft->id, $policy);
        $handler->publishRoleDraft($roleDraft->id);

        $loaded = $handler->loadRole($roleDraft->id);
        $this->assertEquals(
            [
                new Persistence\User\Policy(
                    [
                        'id' => 1,
                        'roleId' => 1,
                        'module' => 'foo',
                        'function' => 'bar',
                        'limitations' => '*',
                        'originalId' => null,
                    ]
                ),
            ],
            $loaded->policies
        );
    }

    public function testLoadRoleWithPoliciesAndGroups()
    {
        $handler = $this->getUserHandler();

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';

        $roleDraft = $handler->createRole($createStruct);

        $policy = new Persistence\User\Policy();
        $policy->module = 'foo';
        $policy->function = 'bar';

        $handler->addPolicyByRoleDraft($roleDraft->id, $policy);

        $handler->assignRole(23, $roleDraft->id);
        $handler->assignRole(42, $roleDraft->id);

        $handler->publishRoleDraft($roleDraft->id);

        $loaded = $handler->loadRole($roleDraft->id);
        $this->assertEquals(
            [
                new Persistence\User\Policy(
                    [
                        'id' => 1,
                        'roleId' => 1,
                        'module' => 'foo',
                        'function' => 'bar',
                        'limitations' => '*',
                        'originalId' => null,
                    ]
                ),
            ],
            $loaded->policies
        );
    }

    public function testLoadRoleWithPolicyLimitations()
    {
        $handler = $this->getUserHandler();

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';

        $roleDraft = $handler->createRole($createStruct);

        $policy = new Persistence\User\Policy();
        $policy->module = 'foo';
        $policy->function = 'bar';
        $policy->limitations = [
            'Subtree' => ['/1', '/1/2'],
            'Foo' => ['Bar'],
        ];

        $handler->addPolicyByRoleDraft($roleDraft->id, $policy);
        $handler->publishRoleDraft($roleDraft->id);

        $loaded = $handler->loadRole($roleDraft->id);
        $this->assertEquals(
            [
                new Persistence\User\Policy(
                    [
                        'id' => 1,
                        'roleId' => 1,
                        'module' => 'foo',
                        'function' => 'bar',
                        'limitations' => [
                            'Subtree' => ['/1', '/1/2'],
                            'Foo' => ['Bar'],
                        ],
                        'originalId' => null,
                    ]
                ),
            ],
            $loaded->policies
        );
    }

    public function testLoadRoles()
    {
        $handler = $this->getUserHandler();

        $this->assertEquals(
            [],
            $handler->loadRoles()
        );

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';

        $roleDraft = $handler->createRole($createStruct);
        $handler->publishRoleDraft($roleDraft->id);
        $role = $handler->loadRole($roleDraft->id);

        $this->assertEquals(
            [$role],
            $handler->loadRoles()
        );
    }

    public function testUpdateRole()
    {
        $handler = $this->getUserHandler();

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';

        $roleDraft = $handler->createRole($createStruct);
        $handler->publishRoleDraft($roleDraft->id);
        $role = $handler->loadRole($roleDraft->id);

        $update = new Persistence\User\RoleUpdateStruct();
        $update->id = $role->id;
        $update->identifier = 'Changed';

        $handler->updateRole($update);

        $this->assertQueryResult(
            [[1, 'Changed']],
            $this->handler->createSelectQuery()->select('id', 'name')->from('ezrole'),
            'Expected a changed role.'
        );
    }

    public function testDeleteRole()
    {
        $this->insertDatabaseFixture(__DIR__ . '/../../../../Repository/Tests/Service/Integration/Legacy/_fixtures/clean_ezdemo_47_dump.php');
        $handler = $this->getUserHandler();

        // 3 is the ID of Editor role
        $handler->deleteRole(3);

        $this->assertQueryResult(
            [],
            $this->handler->createSelectQuery()->select('id')->from('ezrole')->where('id = 3'),
            'Expected an empty set.'
        );

        $this->assertQueryResult(
            [],
            $this->handler->createSelectQuery()->select('role_id')->from('ezpolicy')->where('role_id = 3'),
            'Expected an empty set.'
        );

        $this->assertQueryResult(
            [],
            $this->handler->createSelectQuery()->select('role_id')->from('ezuser_role')->where('role_id = 3'),
            'Expected an empty set.'
        );
    }

    public function testDeleteRoleDraft()
    {
        $this->insertDatabaseFixture(__DIR__ . '/../../../../Repository/Tests/Service/Integration/Legacy/_fixtures/clean_ezdemo_47_dump.php');
        $handler = $this->getUserHandler();

        // 3 is the ID of Editor role
        $roleDraft = $handler->createRoleDraft(3);
        $handler->deleteRole($roleDraft->id, APIRole::STATUS_DRAFT);

        $this->assertQueryResult(
            [['3', APIRole::STATUS_DEFINED]],
            $this->handler->createSelectQuery()->select('id, version')->from('ezrole')->where('id = 3'),
            'Expected a published role.'
        );

        $this->assertQueryResult(
            [[implode("\n", array_fill(0, 28, '3, ' . APIRole::STATUS_DEFINED))]],
            $this->handler->createSelectQuery()->select('role_id, original_id')->from('ezpolicy')->where('role_id = 3'),
            'Expected 28 policies for the published role.'
        );

        $this->assertQueryResult(
            [[3], [3]],
            $this->handler->createSelectQuery()->select('role_id')->from('ezuser_role')->where('role_id = 3'),
            'Expected that role assignments still exist.'
        );
    }

    public function testAddPolicyToRoleLimitations()
    {
        $handler = $this->getUserHandler();

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';

        $roleDraft = $handler->createRole($createStruct);
        $handler->publishRoleDraft($roleDraft->id);
        $role = $handler->loadRole($roleDraft->id);

        $policy = new Persistence\User\Policy();
        $policy->module = 'foo';
        $policy->function = 'bar';

        $handler->addPolicy($role->id, $policy);

        $this->assertQueryResult(
            [[1, 'foo', 'bar', 1]],
            $this->handler->createSelectQuery()->select('id', 'module_name', 'function_name', 'role_id')->from('ezpolicy'),
            'Expected a new policy.'
        );
    }

    public function testAddPolicyPolicyId()
    {
        $handler = $this->getUserHandler();

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';

        $roleDraft = $handler->createRole($createStruct);
        $handler->publishRoleDraft($roleDraft->id);
        $role = $handler->loadRole($roleDraft->id);

        $policy = new Persistence\User\Policy();
        $policy->module = 'foo';
        $policy->function = 'bar';

        $policy = $handler->addPolicy($role->id, $policy);

        $this->assertEquals(1, $policy->id);
    }

    public function testAddPolicyLimitations()
    {
        $handler = $this->getUserHandler();

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';

        $roleDraft = $handler->createRole($createStruct);
        $handler->publishRoleDraft($roleDraft->id);
        $role = $handler->loadRole($roleDraft->id);

        $policy = new Persistence\User\Policy();
        $policy->module = 'foo';
        $policy->function = 'bar';
        $policy->limitations = [
            'Subtree' => ['/1', '/1/2'],
            'Foo' => ['Bar'],
        ];

        $handler->addPolicy($role->id, $policy);

        $this->assertQueryResult(
            [
                [1, 'Subtree', 1],
                [2, 'Foo', 1],
            ],
            $this->handler->createSelectQuery()->select('id', 'identifier', 'policy_id')->from('ezpolicy_limitation'),
            'Expected a new policy.'
        );
    }

    public function testAddPolicyLimitationValues()
    {
        $handler = $this->getUserHandler();

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';

        $roleDraft = $handler->createRole($createStruct);
        $handler->publishRoleDraft($roleDraft->id);
        $role = $handler->loadRole($roleDraft->id);

        $policy = new Persistence\User\Policy();
        $policy->module = 'foo';
        $policy->function = 'bar';
        $policy->limitations = [
            'Subtree' => ['/1', '/1/2'],
            'Foo' => ['Bar'],
        ];

        $handler->addPolicy($role->id, $policy);

        $this->assertQueryResult(
            [
                [1, '/1', 1],
                [2, '/1/2', 1],
                [3, 'Bar', 2],
            ],
            $this->handler->createSelectQuery()->select('id', 'value', 'limitation_id')->from('ezpolicy_limitation_value'),
            'Expected a new policy.'
        );
    }

    protected function createRole()
    {
        $handler = $this->getUserHandler();

        $policy1 = new Persistence\User\Policy();
        $policy1->module = 'foo';
        $policy1->function = 'bar';
        $policy1->limitations = [
            'Subtree' => ['/1', '/1/2'],
            'Foo' => ['Bar'],
        ];

        $policy2 = new Persistence\User\Policy();
        $policy2->module = 'foo';
        $policy2->function = 'blubb';
        $policy2->limitations = [
            'Foo' => ['Blubb'],
        ];

        $createStruct = new Persistence\User\RoleCreateStruct();
        $createStruct->identifier = 'Test';
        $createStruct->policies = [$policy1, $policy2];

        return $handler->createRole($createStruct);
    }

    public function testImplicitlyCreatePolicies()
    {
        $this->createRole();

        $this->assertQueryResult(
            [
                [1, 'foo', 'bar', 1],
                [2, 'foo', 'blubb', 1],
            ],
            $this->handler->createSelectQuery()->select('id', 'module_name', 'function_name', 'role_id')->from('ezpolicy'),
            'Expected a new policy.'
        );
    }

    public function testDeletePolicy()
    {
        $handler = $this->getUserHandler();

        $roleDraft = $this->createRole();
        $handler->publishRoleDraft($roleDraft->id);
        $handler->deletePolicy($roleDraft->policies[0]->id, $roleDraft->policies[0]->roleId);

        $this->assertQueryResult(
            [
                [2, 'foo', 'blubb', 1],
            ],
            $this->handler->createSelectQuery()->select('id', 'module_name', 'function_name', 'role_id')->from('ezpolicy')->where('original_id = 0'),
            'Expected a new policy.'
        );
    }

    public function testDeletePolicyLimitations()
    {
        $handler = $this->getUserHandler();

        $roleDraft = $this->createRole();
        $handler->deletePolicy($roleDraft->policies[0]->id, $roleDraft->policies[0]->roleId);

        $this->assertQueryResult(
            [[3, 'Foo', 2]],
            $this->handler->createSelectQuery()->select('*')->from('ezpolicy_limitation')
        );
    }

    public function testDeletePolicyLimitationValues()
    {
        $handler = $this->getUserHandler();

        $roleDraft = $this->createRole();
        $handler->deletePolicy($roleDraft->policies[0]->id, $roleDraft->policies[0]->roleId);

        $this->assertQueryResult(
            [[4, 3, 'Blubb']],
            $this->handler->createSelectQuery()->select('*')->from('ezpolicy_limitation_value')
        );
    }

    public function testUpdatePolicies()
    {
        $handler = $this->getUserHandler();

        $roleDraft = $this->createRole();

        $policy = $roleDraft->policies[0];
        $policy->limitations = [
            'new' => ['something'],
        ];

        $handler->updatePolicy($policy);

        $this->assertQueryResult(
            [
                [3, 'Foo', 2],
                [4, 'new', 1],
            ],
            $this->handler->createSelectQuery()->select('*')->from('ezpolicy_limitation')
        );

        $this->assertQueryResult(
            [
                [4, 3, 'Blubb'],
                [5, 4, 'something'],
            ],
            $this->handler->createSelectQuery()->select('*')->from('ezpolicy_limitation_value')
        );
    }

    public function testAddRoleToUser()
    {
        $handler = $this->getUserHandler();

        $roleDraft = $this->createRole();
        $handler->publishRoleDraft($roleDraft->id);
        $role = $handler->loadRole($roleDraft->id);
        $handler->create($user = $this->getValidUser());

        $handler->assignRole($user->id, $role->id, []);

        $this->assertQueryResult(
            [
                [1, 42, 1, null, null],
            ],
            $this->handler->createSelectQuery()->select('id', 'contentobject_id', 'role_id', 'limit_identifier', 'limit_value')->from('ezuser_role'),
            'Expected a new user policy association.'
        );
    }

    public function testAddRoleToUserWithLimitation()
    {
        $handler = $this->getUserHandler();

        $roleDraft = $this->createRole();
        $handler->publishRoleDraft($roleDraft->id);
        $role = $handler->loadRole($roleDraft->id);
        $handler->create($user = $this->getValidUser());

        $handler->assignRole(
            $user->id,
            $role->id,
            [
                'Subtree' => ['/1'],
            ]
        );

        $this->assertQueryResult(
            [
                [1, 42, 1, 'Subtree', '/1'],
            ],
            $this->handler->createSelectQuery()->select('id', 'contentobject_id', 'role_id', 'limit_identifier', 'limit_value')->from('ezuser_role'),
            'Expected a new user policy association.'
        );
    }

    public function testAddRoleToUserWithComplexLimitation()
    {
        $handler = $this->getUserHandler();

        $roleDraft = $this->createRole();
        $handler->publishRoleDraft($roleDraft->id);
        $role = $handler->loadRole($roleDraft->id);
        $handler->create($user = $this->getValidUser());

        $handler->assignRole(
            $user->id,
            $role->id,
            [
                'Subtree' => ['/1', '/1/2'],
                'Foo' => ['Bar'],
            ]
        );

        $this->assertQueryResult(
            [
                [1, 42, 1, 'Subtree', '/1'],
                [2, 42, 1, 'Subtree', '/1/2'],
                [3, 42, 1, 'Foo', 'Bar'],
            ],
            $this->handler->createSelectQuery()->select('id', 'contentobject_id', 'role_id', 'limit_identifier', 'limit_value')->from('ezuser_role'),
            'Expected a new user policy association.'
        );
    }

    public function testRemoveUserRoleAssociation()
    {
        $handler = $this->getUserHandler();

        $roleDraft = $this->createRole();
        $handler->publishRoleDraft($roleDraft->id);
        $role = $handler->loadRole($roleDraft->id);
        $handler->create($user = $this->getValidUser());

        $handler->assignRole(
            $user->id,
            $role->id,
            [
                'Subtree' => ['/1', '/1/2'],
                'Foo' => ['Bar'],
            ]
        );

        $handler->unassignRole($user->id, $role->id);

        $this->assertQueryResult(
            [],
            $this->handler->createSelectQuery()->select('id', 'contentobject_id', 'role_id', 'limit_identifier', 'limit_value')->from('ezuser_role'),
            'Expected no user policy associations.'
        );
    }

    public function testLoadPoliciesForUser()
    {
        $this->insertDatabaseFixture(__DIR__ . '/../../../../Repository/Tests/Service/Integration/Legacy/_fixtures/clean_ezdemo_47_dump.php');
        $handler = $this->getUserHandler();

        $policies = $handler->loadPoliciesByUserId(10); // Anonymous user

        // Verify, that we received an array of Policy objects
        $this->assertTrue(
            array_reduce(
                array_map(
                    function ($policy) {
                        return $policy instanceof Persistence\User\Policy;
                    },
                    $policies
                ),
                function ($a, $b) {
                    return $a && $b;
                },
                true
            )
        );
        $this->assertCount(8, $policies);
    }

    public function testLoadRoleAssignmentsByGroupId()
    {
        $this->insertDatabaseFixture(__DIR__ . '/../../../../Repository/Tests/Service/Integration/Legacy/_fixtures/clean_ezdemo_47_dump.php');
        $handler = $this->getUserHandler();

        $this->assertEquals(
            [
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 28,
                        'roleId' => 1,
                        'contentId' => 11,
                    ]
                ),
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 34,
                        'roleId' => 5,
                        'contentId' => 11,
                    ]
                ),
            ],
            $handler->loadRoleAssignmentsByGroupId(11)// 11: Members
        );

        $this->assertEquals(
            [
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 31,
                        'roleId' => 1,
                        'contentId' => 42,
                    ]
                ),
            ],
            $handler->loadRoleAssignmentsByGroupId(42)// 42: Anonymous Users
        );

        $this->assertEquals(
            [],
            $handler->loadRoleAssignmentsByGroupId(10)// 10: Anonymous User
        );
    }

    public function testLoadRoleAssignmentsByGroupIdInherited()
    {
        $this->insertDatabaseFixture(__DIR__ . '/../../../../Repository/Tests/Service/Integration/Legacy/_fixtures/clean_ezdemo_47_dump.php');
        $handler = $this->getUserHandler();

        $this->assertEquals(
            [
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 31,
                        'roleId' => 1,
                        'contentId' => 42,
                    ]
                ),
            ],
            $handler->loadRoleAssignmentsByGroupId(10, true)// 10: Anonymous User
        );
    }

    public function testLoadComplexRoleAssignments()
    {
        $this->insertDatabaseFixture(__DIR__ . '/../../../../Repository/Tests/Service/Integration/Legacy/_fixtures/clean_ezdemo_47_dump.php');
        $handler = $this->getUserHandler();

        $this->assertEquals(
            [
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 32,
                        'roleId' => 3,
                        'contentId' => 13,
                        'limitationIdentifier' => 'Subtree',
                        'values' => ['/1/2/'],
                    ]
                ),
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 33,
                        'roleId' => 3,
                        'contentId' => 13,
                        'limitationIdentifier' => 'Subtree',
                        'values' => ['/1/43/'],
                    ]
                ),
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 38,
                        'roleId' => 5,
                        'contentId' => 13,
                    ]
                ),
            ],
            $handler->loadRoleAssignmentsByGroupId(13)
        );

        $this->assertEquals(
            [
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 32,
                        'roleId' => 3,
                        'contentId' => 13,
                        'limitationIdentifier' => 'Subtree',
                        'values' => ['/1/2/'],
                    ]
                ),
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 33,
                        'roleId' => 3,
                        'contentId' => 13,
                        'limitationIdentifier' => 'Subtree',
                        'values' => ['/1/43/'],
                    ]
                ),
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 38,
                        'roleId' => 5,
                        'contentId' => 13,
                    ]
                ),
            ],
            $handler->loadRoleAssignmentsByGroupId(13, true)
        );
    }

    public function testLoadRoleAssignmentsByRoleId()
    {
        $this->insertDatabaseFixture(__DIR__ . '/../../../../Repository/Tests/Service/Integration/Legacy/_fixtures/clean_ezdemo_47_dump.php');
        $handler = $this->getUserHandler();

        $this->assertEquals(
            [
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 28,
                        'roleId' => 1,
                        'contentId' => 11,
                    ]
                ),
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 31,
                        'roleId' => 1,
                        'contentId' => 42,
                    ]
                ),
                new Persistence\User\RoleAssignment(
                    [
                        'id' => 37,
                        'roleId' => 1,
                        'contentId' => 59,
                    ]
                ),
            ],
            $handler->loadRoleAssignmentsByRoleId(1)
        );
    }

    public function testLoadRoleDraftByRoleId()
    {
        $this->insertDatabaseFixture(__DIR__ . '/../../../../Repository/Tests/Service/Integration/Legacy/_fixtures/clean_ezdemo_47_dump.php');
        $handler = $this->getUserHandler();

        // 3 is the ID of Editor role
        $originalRoleId = 3;
        $draft = $handler->createRoleDraft($originalRoleId);
        $loadedDraft = $handler->loadRoleDraftByRoleId($originalRoleId);
        self::assertSame($loadedDraft->originalId, $originalRoleId);
        self::assertEquals($draft, $loadedDraft);
    }

    public function testRoleDraftOnlyHavePolicyDraft()
    {
        $this->insertDatabaseFixture(__DIR__ . '/../../../../Repository/Tests/Service/Integration/Legacy/_fixtures/clean_ezdemo_47_dump.php');
        $handler = $this->getUserHandler();
        $originalRoleId = 3;
        $originalRole = $handler->loadRole($originalRoleId);
        $originalPolicies = [];
        foreach ($originalRole->policies as $policy) {
            $originalPolicies[$policy->id] = $policy;
        }

        $draft = $handler->createRoleDraft($originalRoleId);
        $loadedDraft = $handler->loadRole($draft->id, Persistence\User\Role::STATUS_DRAFT);
        self::assertSame($loadedDraft->originalId, $originalRoleId);
        self::assertEquals($draft, $loadedDraft);
        foreach ($loadedDraft->policies as $policy) {
            self::assertTrue(isset($originalPolicies[$policy->originalId]));
        }

        // Now add a new policy. Original ID of the new one must be the same as its actual ID.
        $newPolicyModule = 'foo';
        $newPolicyFunction = 'bar';
        $policy = new Persistence\User\Policy(['module' => $newPolicyModule, 'function' => $newPolicyFunction]);
        $policyDraft = $handler->addPolicyByRoleDraft($loadedDraft->id, $policy);

        // Test again by reloading the draft.
        $loadedDraft = $handler->loadRole($draft->id, Persistence\User\Role::STATUS_DRAFT);
        foreach ($loadedDraft->policies as $policy) {
            if ($policy->id != $policyDraft->id) {
                continue;
            }

            self::assertNotNull($policy->originalId);
            self::assertSame($policy->id, $policy->originalId);
        }
    }
}
