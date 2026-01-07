<?php

namespace App\Tests\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityHeadersTest extends WebTestCase
{
    /**
     * Test que tous les headers de sécurité sont présents dans les réponses HTTP
     */
    public function testSecurityHeadersArePresent(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        
        $response = $client->getResponse();
        
        // Vérifier X-XSS-Protection
        $this->assertEquals('1; mode=block', $response->headers->get('X-XSS-Protection'), 
            'X-XSS-Protection header should be present');
        
        // Vérifier X-Content-Type-Options
        $this->assertEquals('nosniff', $response->headers->get('X-Content-Type-Options'), 
            'X-Content-Type-Options header should be present');
        
        // Vérifier X-Frame-Options
        $this->assertEquals('DENY', $response->headers->get('X-Frame-Options'), 
            'X-Frame-Options header should be present');
        
        // Vérifier Content-Security-Policy
        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp, 'Content-Security-Policy header should be present');
        $this->assertStringContainsString("default-src 'self'", $csp, 
            'CSP should contain default-src directive');
        
        // Vérifier Referrer-Policy
        $this->assertEquals('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'), 
            'Referrer-Policy header should be present');
        
        // Vérifier Permissions-Policy
        $permissionsPolicy = $response->headers->get('Permissions-Policy');
        $this->assertNotNull($permissionsPolicy, 'Permissions-Policy header should be present');
        $this->assertStringContainsString('geolocation=()', $permissionsPolicy, 
            'Permissions-Policy should restrict geolocation');
    }

    /**
     * Test que les headers sont présents sur toutes les routes
     */
    public function testSecurityHeadersOnMultipleRoutes(): void
    {
        $client = static::createClient();
        $routes = ['/', '/login', '/register'];
        
        foreach ($routes as $route) {
            $client->request('GET', $route);
            $response = $client->getResponse();
            
            $this->assertNotNull($response->headers->get('X-XSS-Protection'), 
                "X-XSS-Protection should be present on route: {$route}");
            $this->assertNotNull($response->headers->get('X-Content-Type-Options'), 
                "X-Content-Type-Options should be present on route: {$route}");
            $this->assertNotNull($response->headers->get('X-Frame-Options'), 
                "X-Frame-Options should be present on route: {$route}");
        }
    }

    /**
     * Test que le subscriber est bien enregistré
     */
    public function testSecurityHeadersSubscriberIsRegistered(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();
        
        $container = $kernel->getContainer();
        $eventDispatcher = $container->get('event_dispatcher');
        
        $listeners = $eventDispatcher->getListeners('kernel.response');
        $subscriberRegistered = false;
        
        foreach ($listeners as $listener) {
            if (is_array($listener) && is_object($listener[0])) {
                $className = get_class($listener[0]);
                if (str_contains($className, 'SecurityHeadersSubscriber')) {
                    $subscriberRegistered = true;
                    break;
                }
            }
        }
        
        $this->assertTrue($subscriberRegistered, 
            'SecurityHeadersSubscriber should be registered in the event dispatcher');
    }

    /**
     * Test que X-Frame-Options empêche le clickjacking
     */
    public function testClickjackingProtection(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        
        $response = $client->getResponse();
        $frameOptions = $response->headers->get('X-Frame-Options');
        
        $this->assertNotNull($frameOptions, 'X-Frame-Options header should be present');
        $this->assertEquals('DENY', $frameOptions, 
            'X-Frame-Options should be set to DENY to prevent clickjacking');
    }
}
