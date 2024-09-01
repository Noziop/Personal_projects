<?php

use DI\ContainerBuilder;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteParserInterface;
use Slim\Views\Twig;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Flash\Messages;
use GuzzleHttp\Client;
use App\Twig\AppExtension;

// Models
use App\Models\User;
use App\Models\Student;
use App\Models\Cohort;
use App\Models\Drawing;
use App\Models\DrawingDay;
use App\Models\Holiday;
use App\Models\Report;
use App\Models\SODSchedule;
use App\Models\Unavailability;
use App\Models\Vacation;
use App\Models\SODFeedback;
use App\Models\Feedback;
use App\Models\StandupFeedback;

// Controllers
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\UserController;
use App\Controllers\DrawingController;
use App\Controllers\StudentController;
use App\Controllers\CohortController;
use App\Controllers\VacationController;
use App\Controllers\SODFeedbackController;
use App\Controllers\FeedbackController;
use App\Controllers\StandupFeedbackController;

// Services
use App\Services\UserService;
use App\Services\StudentService;
use App\Services\CohortService;
use App\Services\DrawingService;
use App\Services\HolidayService;
use App\Services\ReportService;
use App\Services\SODScheduleService;
use App\Services\UnavailabilityService;
use App\Services\VacationService;
use App\Services\DashboardService;
use App\Services\SODFeedbackService;
use App\Services\FeedbackService;
use App\Services\StandupFeedbackService;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // Core components
        ResponseFactoryInterface::class => fn() => new ResponseFactory(),

        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $loggerSettings = $settings['logger'];
            $logger = new Logger($loggerSettings['name']);
            $logger->pushProcessor(new UidProcessor());
            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);
            return $logger;
        },

        'logger' => function (ContainerInterface $c) {
            return $c->get(LoggerInterface::class);
        },
		'drawingLogger' => function (ContainerInterface $c) {
			$settings = $c->get('settings');
			$loggerSettings = $settings['drawingLogger'];
			$logger = new Logger($loggerSettings['name']);
			$logger->pushProcessor(new UidProcessor());
			$handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
			$logger->pushHandler($handler);
			return $logger;
		},
		Twig::class => function (ContainerInterface $c) {
			$settings = $c->get('settings');
			$twig = Twig::create($settings['view']['template_path'], $settings['view']['twig']);
			$environment = $twig->getEnvironment();
			$environment->addGlobal('session', $_SESSION);
			
			$environment->addFilter(new \Twig\TwigFilter('json_decode', function ($string) {
				return json_decode($string, true);
			}));
		
			return $twig;
		},
        'view' => fn(ContainerInterface $c) => $c->get(Twig::class),

        PDO::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $dbSettings = $settings['db'];
            $dsn = "mysql:host={$dbSettings['host']};dbname={$dbSettings['database']};charset={$dbSettings['charset']}";
            return new PDO($dsn, $dbSettings['username'], $dbSettings['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        },
		'flash' => function () {
			return new Messages();
		},

        // Models
        User::class => fn(ContainerInterface $c) => new User($c->get(PDO::class)),
        Student::class => fn(ContainerInterface $c) => new Student($c->get(PDO::class)),
        Cohort::class => fn(ContainerInterface $c) => new Cohort($c->get(PDO::class)),
        Drawing::class => fn(ContainerInterface $c) => new Drawing($c->get(PDO::class)),
        DrawingDay::class => fn(ContainerInterface $c) => new DrawingDay($c->get(PDO::class)),
        Holiday::class => fn(ContainerInterface $c) => new Holiday($c->get(PDO::class)),
        Report::class => fn(ContainerInterface $c) => new Report($c->get(PDO::class)),
        SODSchedule::class => fn(ContainerInterface $c) => new SODSchedule($c->get(PDO::class)),
        Unavailability::class => fn(ContainerInterface $c) => new Unavailability($c->get(PDO::class)),
        Vacation::class => fn(ContainerInterface $c) => new Vacation($c->get(PDO::class)),
		SODFeedback::class => function (ContainerInterface $c) {
			return new SODFeedback($c->get(PDO::class));
		},
		StandupFeedback::class => fn(ContainerInterface $c) => new StandupFeedback($c->get(PDO::class)),


        // Services
		UserService::class => function (ContainerInterface $c) {
			return new UserService(
				$c->get(User::class),
				$c->get(Student::class),
				$c->get(LoggerInterface::class)
			);
		},
        StudentService::class => function (ContainerInterface $c) {
            return new StudentService(
                $c->get(Student::class),
                $c->get(User::class),
                $c->get(Unavailability::class),
                $c->get(LoggerInterface::class)
            );
        },
        CohortService::class => function (ContainerInterface $c) {
            return new CohortService(
                $c->get(Cohort::class),
                $c->get(DrawingDay::class),
                $c->get(LoggerInterface::class)
            );
        },
		DrawingService::class => function (ContainerInterface $c) {
			return new DrawingService(
				$c->get(Drawing::class),
				$c->get(Student::class),
				$c->get(Cohort::class),
				$c->get(DrawingDay::class),
				$c->get(Vacation::class),
				$c->get(Holiday::class),
				$c->get(Unavailability::class),
				$c->get('drawingLogger')
			);
		},
        HolidayService::class => function (ContainerInterface $c) {
            return new HolidayService($c->get(Holiday::class), $c->get(LoggerInterface::class), new Client());
        },
        ReportService::class => function (ContainerInterface $c) {
            return new ReportService($c->get(Report::class), $c->get(LoggerInterface::class));
        },
        SODScheduleService::class => function (ContainerInterface $c) {
            return new SODScheduleService($c->get(SODSchedule::class), $c->get(LoggerInterface::class));
        },
        UnavailabilityService::class => function (ContainerInterface $c) {
            return new UnavailabilityService($c->get(Unavailability::class), $c->get(LoggerInterface::class));
        },
        VacationService::class => function (ContainerInterface $c) {
            return new VacationService($c->get(Vacation::class), $c->get(LoggerInterface::class));
        },
		DashboardService::class => function (ContainerInterface $c) {
			return new DashboardService(
				$c->get(CohortService::class),
				$c->get(StudentService::class),
				$c->get(SODScheduleService::class),
				$c->get(ReportService::class),
				$c->get(UserService::class),
				$c->get(VacationService::class),
				$c->get(LoggerInterface::class)
			);
		},
		SODFeedbackService::class => function (ContainerInterface $c) {
			return new SODFeedbackService(
				$c->get(SODFeedback::class),
				$c->get(LoggerInterface::class)
			);
		},
		FeedbackService::class => function (ContainerInterface $c) {
			return new FeedbackService(
				$c->get(Feedback::class),
				$c->get(Student::class),
				$c->get(User::class),
				$c->get(StandupFeedback::class),
				$c->get(Cohort::class),
				$c->get(LoggerInterface::class)
			);
		},
		StandupFeedbackService::class => function (ContainerInterface $c) {
			return new StandupFeedbackService(
				$c->get(StandupFeedback::class),
				$c->get(LoggerInterface::class)
			);
		},

        // Controllers
        AuthController::class => function (ContainerInterface $c) {
            return new AuthController($c->get(Twig::class), $c->get(UserService::class), $c->get(LoggerInterface::class));
        },
        DashboardController::class => function (ContainerInterface $c) {
            return new DashboardController($c->get(Twig::class), $c->get(LoggerInterface::class), $c->get(DashboardService::class));
        },
        UserController::class => function (ContainerInterface $c) {
            return new UserController($c->get(Twig::class), $c->get(LoggerInterface::class), $c->get(UserService::class));
        },
		DrawingController::class => function (ContainerInterface $c) {
			return new DrawingController(
				$c->get('view'),
				$c->get(DrawingService::class),
				$c->get(CohortService::class),
				$c->get('drawingLogger'),
				$c->get('flash')
			);
		},
        StudentController::class => function (ContainerInterface $c) {
            return new StudentController(
                $c->get(Twig::class),
                $c->get(StudentService::class),
                $c->get(CohortService::class),
                $c->get(UserService::class),
                $c->get(LoggerInterface::class)
            );
        },
        CohortController::class => function (ContainerInterface $c) {
            return new CohortController($c->get(Twig::class), $c->get(CohortService::class), $c->get(LoggerInterface::class));
        },
		VacationController::class => function (ContainerInterface $c) {
			return new VacationController(
				$c->get(Twig::class),
				$c->get(VacationService::class),
				$c->get(CohortService::class),
				$c->get(LoggerInterface::class)
			);
		},
		SODFeedbackController::class => function (ContainerInterface $c) {
			return new SODFeedbackController(
				$c->get(Twig::class),
				$c->get(SODFeedbackService::class),
				$c->get(UserService::class),
				$c->get(LoggerInterface::class)
			);
		},
		FeedbackController::class => function (ContainerInterface $c) {
			return new FeedbackController(
				$c->get(Twig::class),
				$c->get(FeedbackService::class),
				$c->get(LoggerInterface::class)
			);
		},
		StandupFeedbackController::class => function (ContainerInterface $c) {
			return new StandupFeedbackController(
				$c->get(Twig::class),
				$c->get(StandupFeedbackService::class),
				$c->get(StudentService::class),
				$c->get(UserService::class),
				$c->get(CohortService::class),
				$c->get(LoggerInterface::class)
			);
		},

        // Application
        App::class => function (ContainerInterface $c) {
            AppFactory::setContainer($c);
            return AppFactory::create();
        },

        RouteParserInterface::class => fn(ContainerInterface $c) => $c->get(App::class)->getRouteCollector()->getRouteParser(),
    ]);
};