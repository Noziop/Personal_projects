<?php

namespace App\Controllers;

use App\Services\UserService;
use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\DuplicateUserException;

class UserController
{
    private $userService;
    private $logger;
    private $view;

    public function __construct(UserService $userService, LoggerInterface $logger, Twig $view)
    {
        $this->userService = $userService;
        $this->logger = $logger;
        $this->view = $view;
    }

    public function index(Request $request, Response $response): Response
    {
        try {
            $users = $this->userService->getAllUsers();
            return $this->view->render($response, 'users/index.twig', ['users' => $users]);
        } catch (\Exception $e) {
            $this->logger->error('Error fetching users: ' . $e->getMessage());
            return $this->view->render($response, 'error.twig', ['error' => 'Une erreur est survenue lors de la récupération des utilisateurs.'])->withStatus(500);
        }
    }

    public function create(Request $request, Response $response): Response
    {
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $user = new User($data);

            try {
                $this->userService->createUser($user);
                $this->logger->info('User created: ' . $user->getUsername());
                return $response->withHeader('Location', '/users')->withStatus(302);
            } catch (DuplicateUserException $e) {
                $this->logger->warning('Duplicate user creation attempt: ' . $e->getMessage());
                return $this->view->render($response, 'users/create.twig', ['error' => $e->getMessage(), 'user' => $user])->withStatus(400);
            } catch (\Exception $e) {
                $this->logger->error('Error creating user: ' . $e->getMessage());
                return $this->view->render($response, 'users/create.twig', ['error' => 'Une erreur est survenue lors de la création de l\'utilisateur.', 'user' => $user])->withStatus(500);
            }
        }

        return $this->view->render($response, 'users/create.twig');
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];

        try {
            $user = $this->userService->getUserById($id);

            if ($request->getMethod() === 'POST') {
                $data = $request->getParsedBody();
                $user->hydrate($data);

                $this->userService->updateUser($user);
                $this->logger->info('User updated: ' . $user->getUsername());
                return $response->withHeader('Location', '/users')->withStatus(302);
            }

            return $this->view->render($response, 'users/edit.twig', ['user' => $user]);
        } catch (UserNotFoundException $e) {
            $this->logger->warning('User not found: ' . $id);
            return $this->view->render($response, 'error.twig', ['error' => 'Utilisateur non trouvé.'])->withStatus(404);
        } catch (\Exception $e) {
            $this->logger->error('Error editing user: ' . $e->getMessage());
            return $this->view->render($response, 'error.twig', ['error' => 'Une erreur est survenue lors de la modification de l\'utilisateur.'])->withStatus(500);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];

        try {
            $this->userService->deleteUser($id);
            $this->logger->info('User deleted: ' . $id);
            return $response->withHeader('Location', '/users')->withStatus(302);
        } catch (UserNotFoundException $e) {
            $this->logger->warning('Attempt to delete non-existent user: ' . $id);
            return $this->view->render($response, 'error.twig', ['error' => 'Utilisateur non trouvé.'])->withStatus(404);
        } catch (\Exception $e) {
            $this->logger->error('Error deleting user: ' . $e->getMessage());
            return $this->view->render($response, 'error.twig', ['error' => 'Une erreur est survenue lors de la suppression de l\'utilisateur.'])->withStatus(500);
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];

        try {
            $user = $this->userService->getUserById($id);
            return $this->view->render($response, 'users/show.twig', ['user' => $user]);
        } catch (UserNotFoundException $e) {
            $this->logger->warning('User not found: ' . $id);
            return $this->view->render($response, 'error.twig', ['error' => 'Utilisateur non trouvé.'])->withStatus(404);
        } catch (\Exception $e) {
            $this->logger->error('Error fetching user: ' . $e->getMessage());
            return $this->view->render($response, 'error.twig', ['error' => 'Une erreur est survenue lors de la récupération de l\'utilisateur.'])->withStatus(500);
        }
    }
}
