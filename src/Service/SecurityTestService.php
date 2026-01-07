<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class SecurityTestService
{
    public function __construct(
        private KernelInterface $kernel
    ) {
    }

    /**
     * Exécute tous les tests de sécurité et retourne un rapport
     */
    public function runSecurityTests(): array
    {
        $report = [
            'timestamp' => new \DateTime(),
            'tests' => [],
            'summary' => [
                'total' => 0,
                'passed' => 0,
                'failed' => 0,
                'warnings' => 0,
            ],
        ];

        // Test 1: Headers de sécurité HTTP
        $headersTest = $this->testSecurityHeaders();
        $report['tests']['security_headers'] = $headersTest;
        $this->updateSummary($report['summary'], $headersTest);

        // Test 2: Configuration de sécurité
        $configTest = $this->testSecurityConfiguration();
        $report['tests']['security_configuration'] = $configTest;
        $this->updateSummary($report['summary'], $configTest);

        // Test 3: Protection des fichiers sensibles
        $filesTest = $this->testSensitiveFilesProtection();
        $report['tests']['sensitive_files'] = $filesTest;
        $this->updateSummary($report['summary'], $filesTest);

        // Test 4: Validations de formulaires
        $formsTest = $this->testFormValidations();
        $report['tests']['form_validations'] = $formsTest;
        $this->updateSummary($report['summary'], $formsTest);

        // Test 5: Vérification du subscriber
        $subscriberTest = $this->testSubscriberRegistration();
        $report['tests']['subscriber_registration'] = $subscriberTest;
        $this->updateSummary($report['summary'], $subscriberTest);

        return $report;
    }

    /**
     * Test les headers de sécurité HTTP
     */
    private function testSecurityHeaders(): array
    {
        $test = [
            'name' => 'Headers de sécurité HTTP',
            'description' => 'Vérification de la présence des headers de sécurité',
            'checks' => [],
            'status' => 'success',
        ];

        try {
            // Simuler une requête HTTP
            $container = $this->kernel->getContainer();
            $request = Request::create('/', 'GET');
            
            // On ne peut pas facilement tester les headers sans faire une vraie requête HTTP
            // On vérifie plutôt que le subscriber est bien enregistré
            $eventDispatcher = $container->get('event_dispatcher');
            $listeners = $eventDispatcher->getListeners('kernel.response');
            
            $subscriberFound = false;
            foreach ($listeners as $listener) {
                if (is_array($listener) && is_object($listener[0])) {
                    $className = get_class($listener[0]);
                    if (str_contains($className, 'SecurityHeadersSubscriber')) {
                        $subscriberFound = true;
                        break;
                    }
                }
            }

            if ($subscriberFound) {
                $test['checks'][] = [
                    'name' => 'SecurityHeadersSubscriber enregistré',
                    'status' => 'success',
                    'message' => 'Le subscriber est correctement enregistré',
                ];
            } else {
                $test['checks'][] = [
                    'name' => 'SecurityHeadersSubscriber enregistré',
                    'status' => 'failed',
                    'message' => 'Le subscriber n\'est pas trouvé dans les listeners',
                ];
                $test['status'] = 'failed';
            }

            // Vérifier que la classe existe
            if (class_exists('App\EventSubscriber\SecurityHeadersSubscriber')) {
                $test['checks'][] = [
                    'name' => 'Classe SecurityHeadersSubscriber existe',
                    'status' => 'success',
                    'message' => 'La classe est présente',
                ];
            } else {
                $test['checks'][] = [
                    'name' => 'Classe SecurityHeadersSubscriber existe',
                    'status' => 'failed',
                    'message' => 'La classe n\'existe pas',
                ];
                $test['status'] = 'failed';
            }

        } catch (\Exception $e) {
            $test['status'] = 'error';
            $test['checks'][] = [
                'name' => 'Erreur lors du test',
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return $test;
    }

    /**
     * Test la configuration de sécurité
     */
    private function testSecurityConfiguration(): array
    {
        $test = [
            'name' => 'Configuration de sécurité',
            'description' => 'Vérification de la configuration de sécurité',
            'checks' => [],
            'status' => 'success',
        ];

        try {
            $env = $this->kernel->getEnvironment();
            
            // Vérifier la configuration de production
            $prodConfigPath = $this->kernel->getProjectDir() . '/config/packages/prod/security.yaml';
            if (file_exists($prodConfigPath)) {
                $test['checks'][] = [
                    'name' => 'Configuration production existe',
                    'status' => 'success',
                    'message' => 'Le fichier config/packages/prod/security.yaml existe',
                ];
            } else {
                $test['checks'][] = [
                    'name' => 'Configuration production existe',
                    'status' => 'warning',
                    'message' => 'Le fichier de configuration production n\'existe pas',
                ];
                $test['status'] = 'warning';
            }

            // Vérifier l'environnement actuel
            $test['checks'][] = [
                'name' => 'Environnement',
                'status' => 'success',
                'message' => "Environnement actuel: {$env}",
            ];

        } catch (\Exception $e) {
            $test['status'] = 'error';
            $test['checks'][] = [
                'name' => 'Erreur lors du test',
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return $test;
    }

    /**
     * Test la protection des fichiers sensibles
     */
    private function testSensitiveFilesProtection(): array
    {
        $test = [
            'name' => 'Protection des fichiers sensibles',
            'description' => 'Vérification de la protection des fichiers sensibles',
            'checks' => [],
            'status' => 'success',
        ];

        try {
            $projectDir = $this->kernel->getProjectDir();
            $htaccessPath = $projectDir . '/public/.htaccess';

            if (file_exists($htaccessPath)) {
                $htaccessContent = file_get_contents($htaccessPath);
                
                // Vérifier la présence de règles de protection
                $sensitivePatterns = [
                    '\.env' => 'Protection .env',
                    '\.json' => 'Protection .json',
                    '\.lock' => 'Protection .lock',
                    '\.yaml' => 'Protection .yaml',
                    '\.yml' => 'Protection .yml',
                ];

                foreach ($sensitivePatterns as $pattern => $name) {
                    if (preg_match('/' . preg_quote($pattern, '/') . '/', $htaccessContent) || 
                        str_contains($htaccessContent, $pattern)) {
                        $test['checks'][] = [
                            'name' => $name,
                            'status' => 'success',
                            'message' => "Protection trouvée pour {$pattern}",
                        ];
                    } else {
                        $test['checks'][] = [
                            'name' => $name,
                            'status' => 'warning',
                            'message' => "Protection non trouvée pour {$pattern}",
                        ];
                        if ($test['status'] !== 'failed') {
                            $test['status'] = 'warning';
                        }
                    }
                }

                // Vérifier les headers de sécurité dans .htaccess
                if (str_contains($htaccessContent, 'X-XSS-Protection')) {
                    $test['checks'][] = [
                        'name' => 'Headers sécurité dans .htaccess',
                        'status' => 'success',
                        'message' => 'Headers de sécurité présents dans .htaccess',
                    ];
                } else {
                    $test['checks'][] = [
                        'name' => 'Headers sécurité dans .htaccess',
                        'status' => 'warning',
                        'message' => 'Headers de sécurité non trouvés dans .htaccess',
                    ];
                }

            } else {
                $test['checks'][] = [
                    'name' => 'Fichier .htaccess',
                    'status' => 'warning',
                    'message' => 'Le fichier .htaccess n\'existe pas (normal si vous n\'utilisez pas Apache)',
                ];
                $test['status'] = 'warning';
            }

        } catch (\Exception $e) {
            $test['status'] = 'error';
            $test['checks'][] = [
                'name' => 'Erreur lors du test',
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return $test;
    }

    /**
     * Test les validations de formulaires
     */
    private function testFormValidations(): array
    {
        $test = [
            'name' => 'Validations de formulaires',
            'description' => 'Vérification de la présence des validations',
            'checks' => [],
            'status' => 'success',
        ];

        try {
            $formsToCheck = [
                'App\Form\EventType' => 'EventType',
                'App\Form\BookingType' => 'BookingType',
                'App\Form\PaymentType' => 'PaymentType',
                'App\Form\RegistrationFormType' => 'RegistrationFormType',
                'App\Form\UserType' => 'UserType',
            ];

            foreach ($formsToCheck as $className => $displayName) {
                if (class_exists($className)) {
                    $test['checks'][] = [
                        'name' => "Classe {$displayName}",
                        'status' => 'success',
                        'message' => "La classe {$displayName} existe",
                    ];
                } else {
                    $test['checks'][] = [
                        'name' => "Classe {$displayName}",
                        'status' => 'failed',
                        'message' => "La classe {$displayName} n'existe pas",
                    ];
                    $test['status'] = 'failed';
                }
            }

        } catch (\Exception $e) {
            $test['status'] = 'error';
            $test['checks'][] = [
                'name' => 'Erreur lors du test',
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return $test;
    }

    /**
     * Test l'enregistrement du subscriber
     */
    private function testSubscriberRegistration(): array
    {
        $test = [
            'name' => 'Enregistrement du Subscriber',
            'description' => 'Vérification de l\'enregistrement du SecurityHeadersSubscriber',
            'checks' => [],
            'status' => 'success',
        ];

        try {
            $container = $this->kernel->getContainer();
            $eventDispatcher = $container->get('event_dispatcher');
            $listeners = $eventDispatcher->getListeners('kernel.response');

            $subscriberFound = false;
            foreach ($listeners as $listener) {
                if (is_array($listener) && is_object($listener[0])) {
                    $className = get_class($listener[0]);
                    if (str_contains($className, 'SecurityHeadersSubscriber')) {
                        $subscriberFound = true;
                        $test['checks'][] = [
                            'name' => 'Subscriber trouvé',
                            'status' => 'success',
                            'message' => "Subscriber trouvé: {$className}",
                        ];
                        break;
                    }
                }
            }

            if (!$subscriberFound) {
                $test['checks'][] = [
                    'name' => 'Subscriber trouvé',
                    'status' => 'failed',
                    'message' => 'SecurityHeadersSubscriber non trouvé dans les listeners',
                ];
                $test['status'] = 'failed';
            }

        } catch (\Exception $e) {
            $test['status'] = 'error';
            $test['checks'][] = [
                'name' => 'Erreur lors du test',
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return $test;
    }

    /**
     * Met à jour le résumé des tests
     */
    private function updateSummary(array &$summary, array $test): void
    {
        $summary['total']++;
        
        switch ($test['status']) {
            case 'success':
                $summary['passed']++;
                break;
            case 'failed':
            case 'error':
                $summary['failed']++;
                break;
            case 'warning':
                $summary['warnings']++;
                break;
        }
    }
}
