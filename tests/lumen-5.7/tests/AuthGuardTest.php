<?php

/**
 * This file is part of Http Basic Auth Guard.
 *
 * (c) Christopher Lass <arubacao@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Arubacao\BasicAuth\BasicGuard;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Events\Dispatcher;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Contracts\Auth\Authenticatable;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthGuardTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testBasicReturnsNullOnValidAttempt()
    {
        [$provider, $request] = $this->getMocks();
        $guard = m::mock(BasicGuard::class.'[check,attempt]', ['default', $provider]);
        $guard->shouldReceive('check')->once()->andReturn(false);
        $guard->shouldReceive('attempt')->once()->with(['email' => 'foo@bar.com', 'password' => 'secret'])->andReturn(true);
        $request = Request::create('/', 'GET', [], [], [], ['PHP_AUTH_USER' => 'foo@bar.com', 'PHP_AUTH_PW' => 'secret']);
        $guard->setRequest($request);

        $guard->basic('email');
    }

    public function testBasicReturnsNullWhenAlreadyLoggedIn()
    {
        [$provider, $request] = $this->getMocks();
        $guard = m::mock(BasicGuard::class.'[check,attempt]', ['default', $provider]);
        $guard->shouldReceive('check')->once()->andReturn(true);
        $guard->shouldReceive('attempt')->never();
        $request = Request::create('/', 'GET', [], [], [], ['PHP_AUTH_USER' => 'foo@bar.com', 'PHP_AUTH_PW' => 'secret']);
        $guard->setRequest($request);

        $guard->basic('email');
    }

    public function testBasicReturnsResponseOnFailure()
    {
        $this->expectException(UnauthorizedHttpException::class);

        [$provider, $request] = $this->getMocks();
        $guard = m::mock(BasicGuard::class.'[check,attempt]', ['default', $provider]);
        $guard->shouldReceive('check')->once()->andReturn(false);
        $guard->shouldReceive('attempt')->once()->with(['email' => 'foo@bar.com', 'password' => 'secret'])->andReturn(false);
        $request = Request::create('/', 'GET', [], [], [], ['PHP_AUTH_USER' => 'foo@bar.com', 'PHP_AUTH_PW' => 'secret']);
        $guard->setRequest($request);
        $guard->basic('email');
    }

    public function testBasicWithExtraConditions()
    {
        [$provider, $request] = $this->getMocks();
        $guard = m::mock(BasicGuard::class.'[check,attempt]', ['default', $provider]);
        $guard->shouldReceive('check')->once()->andReturn(false);
        $guard->shouldReceive('attempt')->once()->with(['email' => 'foo@bar.com', 'password' => 'secret', 'active' => 1])->andReturn(true);
        $request = Request::create('/', 'GET', [], [], [], ['PHP_AUTH_USER' => 'foo@bar.com', 'PHP_AUTH_PW' => 'secret']);
        $guard->setRequest($request);

        $guard->basic('email', ['active' => 1]);
    }

    public function testBasicWithExtraArrayConditions()
    {
        [$provider, $request] = $this->getMocks();
        $guard = m::mock(BasicGuard::class.'[check,attempt]', ['default', $provider]);
        $guard->shouldReceive('check')->once()->andReturn(false);
        $guard->shouldReceive('attempt')->once()->with(['email' => 'foo@bar.com', 'password' => 'secret', 'active' => 1, 'type' => [1, 2, 3]])->andReturn(true);
        $request = Request::create('/', 'GET', [], [], [], ['PHP_AUTH_USER' => 'foo@bar.com', 'PHP_AUTH_PW' => 'secret']);
        $guard->setRequest($request);

        $guard->basic('email', ['active' => 1, 'type' => [1, 2, 3]]);
    }

    public function testAttemptCallsRetrieveByCredentials()
    {
        $guard = $this->getGuard();
        $guard->setDispatcher($events = m::mock(Dispatcher::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(Attempting::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(Failed::class));
        $guard->getProvider()->shouldReceive('retrieveByCredentials')->once()->with(['foo']);
        $guard->attempt(['foo']);
    }

    public function testAttemptReturnsUserInterface()
    {
        [$provider, $request] = $this->getMocks();
        $guard = $this->getMockBuilder(BasicGuard::class)->setMethods(['login'])->setConstructorArgs(['default', $provider, $request])->getMock();
        $guard->setDispatcher($events = m::mock(Dispatcher::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(Attempting::class));
        $user = $this->createMock(Authenticatable::class);
        $guard->getProvider()->shouldReceive('retrieveByCredentials')->once()->andReturn($user);
        $guard->getProvider()->shouldReceive('validateCredentials')->with($user, ['foo'])->andReturn(true);
        $guard->expects($this->once())->method('login')->with($this->equalTo($user));
        $this->assertTrue($guard->attempt(['foo']));
    }

    public function testAttemptReturnsFalseIfUserNotGiven()
    {
        $mock = $this->getGuard();
        $mock->setDispatcher($events = m::mock(Dispatcher::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(Attempting::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(Failed::class));
        $mock->getProvider()->shouldReceive('retrieveByCredentials')->once()->andReturn(null);
        $this->assertFalse($mock->attempt(['foo']));
    }

    public function testBasicGuardIsMacroable()
    {
        $guard = $this->getGuard();

        $guard->macro('foo', function () {
            return 'bar';
        });

        $this->assertEquals(
            'bar', $guard->foo()
        );
    }

    public function testFailedAttemptFiresFailedEvent()
    {
        $guard = $this->getGuard();
        $guard->setDispatcher($events = m::mock(Dispatcher::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(Attempting::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(Failed::class));
        $guard->getProvider()->shouldReceive('retrieveByCredentials')->once()->with(['foo'])->andReturn(null);
        $guard->attempt(['foo']);
    }

    public function testAuthenticateReturnsUserWhenUserIsNotNull()
    {
        $user = m::mock(Authenticatable::class);
        $guard = $this->getGuard()->setUser($user);

        $this->assertEquals($user, $guard->authenticate());
    }

    public function testSetUserFiresAuthenticatedEvent()
    {
        $user = m::mock(Authenticatable::class);
        $guard = $this->getGuard();
        $guard->setDispatcher($events = m::mock(Dispatcher::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(Authenticated::class));
        $guard->setUser($user);
    }

    public function testHasUserReturnsTrueWhenUserIsNotNull()
    {
        $user = m::mock(Authenticatable::class);
        $guard = $this->getGuard()->setUser($user);

        $this->assertTrue($guard->hasUser());
    }

    public function testHasUserReturnsFalseWhenUserIsNull()
    {
        $guard = $this->getGuard();

        $this->assertFalse($guard->hasUser());
    }

    public function testIsAuthedReturnsTrueWhenUserIsNotNull()
    {
        $user = m::mock(Authenticatable::class);
        $mock = $this->getGuard();
        $mock->setUser($user);
        $this->assertTrue($mock->check());
        $this->assertFalse($mock->guest());
    }

    public function testIsAuthedReturnsFalseWhenUserIsNull()
    {
        [$provider, $request] = $this->getMocks();
        $mock = $this->getMockBuilder(BasicGuard::class)->setMethods(['user'])->setConstructorArgs(['default', $provider, $request])->getMock();
        $mock->expects($this->exactly(2))->method('user')->will($this->returnValue(null));
        $this->assertFalse($mock->check());
        $this->assertTrue($mock->guest());
    }

    public function testUserMethodReturnsCachedUser()
    {
        $user = m::mock(Authenticatable::class);
        $mock = $this->getGuard();
        $mock->setUser($user);
        $this->assertSame($user, $mock->user());
    }

    public function testLoginUsingIdLogsInWithUser()
    {
        [$provider, $request] = $this->getMocks();

        $guard = m::mock(BasicGuard::class, ['default', $provider])->makePartial();

        $user = m::mock(Authenticatable::class);
        $guard->getProvider()->shouldReceive('retrieveById')->once()->with(10)->andReturn($user);
        $guard->shouldReceive('login')->once()->with($user, false);

        $this->assertSame($user, $guard->loginUsingId(10));
    }

    public function testLoginUsingIdFailure()
    {
        [$provider, $request] = $this->getMocks();
        $guard = m::mock(BasicGuard::class, ['default', $provider])->makePartial();

        $guard->getProvider()->shouldReceive('retrieveById')->once()->with(11)->andReturn(null);
        $guard->shouldNotReceive('login');

        $this->assertFalse($guard->loginUsingId(11));
    }

    public function testOnceUsingIdSetsUser()
    {
        [$provider, $request] = $this->getMocks();
        $guard = m::mock(BasicGuard::class, ['default', $provider])->makePartial();

        $user = m::mock(Authenticatable::class);
        $guard->getProvider()->shouldReceive('retrieveById')->once()->with(10)->andReturn($user);
        $guard->shouldReceive('setUser')->once()->with($user);

        $this->assertSame($user, $guard->onceUsingId(10));
    }

    public function testOnceUsingIdFailure()
    {
        [$provider, $request] = $this->getMocks();
        $guard = m::mock(BasicGuard::class, ['default', $provider])->makePartial();

        $guard->getProvider()->shouldReceive('retrieveById')->once()->with(11)->andReturn(null);
        $guard->shouldNotReceive('setUser');

        $this->assertFalse($guard->onceUsingId(11));
    }

    public function testLoginOnceSetsUser()
    {
        [$provider, $request] = $this->getMocks();
        $guard = m::mock(BasicGuard::class, ['default', $provider])->makePartial();
        $user = m::mock(Authenticatable::class);
        $guard->getProvider()->shouldReceive('retrieveByCredentials')->once()->with(['foo'])->andReturn($user);
        $guard->getProvider()->shouldReceive('validateCredentials')->once()->with($user, ['foo'])->andReturn(true);
        $guard->shouldReceive('setUser')->once()->with($user);
        $this->assertTrue($guard->once(['foo']));
    }

    public function testLoginOnceFailure()
    {
        [$provider, $request] = $this->getMocks();
        $guard = m::mock(BasicGuard::class, ['default', $provider])->makePartial();
        $user = m::mock(Authenticatable::class);
        $guard->getProvider()->shouldReceive('retrieveByCredentials')->once()->with(['foo'])->andReturn($user);
        $guard->getProvider()->shouldReceive('validateCredentials')->once()->with($user, ['foo'])->andReturn(false);
        $this->assertFalse($guard->once(['foo']));
    }

    protected function getGuard()
    {
        [$provider, $request] = $this->getMocks();

        return new BasicGuard('default', $provider, $request);
    }

    protected function getMocks()
    {
        return [
            m::mock(UserProvider::class),
            Request::create('/', 'GET'),
        ];
    }

}
