<?php

namespace Tests\Unit;

use Ringierimu\StateWorkflow\Workflow\StateWorkflow;
use Symfony\Component\Workflow\Exception\NotEnabledTransitionException;
use Tests\Fixtures\Models\User;
use Tests\TestCase;

/**
 * Class UserTest.
 */
class UserUnitTest extends TestCase
{
    /** @var User */
    protected $user;

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->user = factory(User::class)->create([
            'user_state' => 'new',
        ]);

        auth()->login(factory(User::class)->create([
            'user_state' => 'new',
        ]));
    }

    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function it_return_workflow_instance()
    {
        $this->assertInstanceOf(StateWorkflow::class, $this->user->workflow());
    }

    /**
     * Test current state to be new.
     *
     * @test
     *
     * @throws \ReflectionException
     */
    public function is_current_state_new()
    {
        $this->assertEquals('new', $this->user->state());
    }

    /**
     * Test if transition can be applied or not.
     *
     * @test
     *
     * @throws \ReflectionException
     */
    public function can_apply_transition()
    {
        $this->assertTrue($this->user->canTransition('create'));
        $this->assertFalse($this->user->canTransition('block'));
    }

    /**
     * Test invalid transition.
     *
     * @test
     *
     * @throws \ReflectionException
     */
    public function invalid_transition_throws_exception()
    {
        $this->expectException(NotEnabledTransitionException::class);

        $this->user->applyTransition('block');
    }

    /**
     * Change Model states by applying transitions.
     *
     * @test
     *
     * @throws \ReflectionException
     */
    public function apply_transitions()
    {
        $this->user->applyTransition('create');
        $this->user = $this->user->refresh();
        $this->assertEquals('pending_activation', $this->user->state());
        $this->assertEquals(1, $this->user->stateHistory()->count());

        $this->user->applyTransition('activate');
        $this->user = $this->user->refresh();
        $this->assertEquals('activated', $this->user->state());
        $this->assertEquals(2, $this->user->stateHistory()->count());

        $this->user->applyTransition('block');
        $this->user = $this->user->refresh();
        $this->assertEquals('blocked', $this->user->state());
        $this->assertEquals(3, $this->user->stateHistory()->count());

        $this->user->applyTransition('delete');
        $this->user = $this->user->refresh();
        $this->assertEquals('deleted', $this->user->state());
        $this->assertEquals(4, $this->user->stateHistory()->count());
    }
}
