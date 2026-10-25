<?php
namespace Tests\Feature;
use App\Http\Middleware\CheckQaAccess;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Tests\TestCase;

class WhClassScheduleAccessTest extends TestCase
{
    private function req(string $name, string $role): Request
    {
        $u = new User(); $u->id = 31; $u->role = $role;
        $r = Request::create('/sales/prototype/wh-class-schedule', 'POST');
        $route = new Route(['POST'], '/sales/prototype/wh-class-schedule', fn () => null);
        $route->name($name);
        $r->setRouteResolver(fn () => $route);
        $r->setUserResolver(fn () => $u);
        return $r;
    }
    public function test_qa_can_save_schedule(): void
    {
        $mw = new CheckQaAccess();
        $res = $mw->handle($this->req('sales.prototype.wh-class-schedule.save','qa'), fn ($r) => response('ok'));
        $this->assertSame(200, $res->getStatusCode());
    }
}
