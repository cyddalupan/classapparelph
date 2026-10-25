<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckQaAccess;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Tests\TestCase;

class QaProgressRouteAccessTest extends TestCase
{
    private function makeRequest(string $routeName): Request
    {
        $user = new User();
        $user->id = 31;
        $user->role = 'qa';

        $req = Request::create('/sales/prototype/1/progress', 'GET');
        $route = new Route(['GET'], '/sales/prototype/{id}/progress', fn () => null);
        $route->name($routeName);
        $req->setRouteResolver(fn () => $route);
        $req->setUserResolver(fn () => $user);

        return $req;
    }

    public function test_qa_can_access_progress_route(): void
    {
        $mw = new CheckQaAccess();
        $res = $mw->handle($this->makeRequest('sales.prototype.progress'), fn ($r) => response('ok'));
        $this->assertSame(200, $res->getStatusCode());
    }

    public function test_qa_still_blocked_on_unlisted_route(): void
    {
        $mw = new CheckQaAccess();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $mw->handle($this->makeRequest('sales.prototype.destroy'), fn ($r) => response('ok'));
    }
}
