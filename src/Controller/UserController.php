<?php

namespace App\Controller;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/user", methods={"GET"})
     */
    public function allUsers(Request $request)
    {
        //проверяем авторизацию - наличие валидного хэша
        $hasAccess = $this->checkAccess($request);
        if (!$hasAccess) {
            return new JsonResponse(['error' => 'Unauthorized. Access denied'], 401);
        }

        $dbh = $this->getDoctrine()->getConnection();
        $users = $dbh->fetchAllAssociative("SELECT first_name, last_name, nickname, email, age FROM users");

        return new JsonResponse($users);
    }

    /**
     * @Route("/user/{id}", methods={"GET"})
     */
    public function userById($id, Request $request)
    {
        //проверяем авторизацию - наличие валидного хэша
        $hasAccess = $this->checkAccess($request);
        if (!$hasAccess) {
            return new JsonResponse(['error' => 'Unauthorized. Access denied'], 401);
        }
        if (!is_numeric($id)) {
            return new JsonResponse(['error' => 'Id must be integer'], 401);
        }

        //выпоняем поск в базе
        $dbh = $this->getDoctrine()->getConnection();
        $query = $dbh->fetchAllAssociative("SELECT id, first_name, last_name, nickname, email, age 
        FROM users WHERE id=" . $id);

        //если результат не пустой, возвращаем результат и код
        if ($query) {
            return new JsonResponse(array_shift($query));
        }

        return new JsonResponse(['query' => "search for id={$id}", 'error' => 'NOT FOUND'], 404);
    }

    /**
     * @Route("/user", methods={"PUT"})
     */
    public function addUser(Request $request)
    {
        //записываем тело запроса в массив
        $jsonRequest = json_decode($request->getContent(), true);

        //проверяем корректность формата данных
        if (!$jsonRequest) {
            return new JsonResponse(['error' => 'Unsupported format data'], 400);
        }

        //проверка значений: переписываем ключи массива в значения...
        foreach ($jsonRequest as $key => $item) {
            $sentParams[] = $key;
        }

        //формируем "правильный" список значений...
        $required = [
            'firstname',
            'lastname',
            'nickname',
            'email',
            'age',
            'password'
        ];

        //...и сравниваем значения массивов
        $diff = array_diff($sentParams, $required);

        //если хоть одно значение отличается или отсутствует (результат сравнения не пустой), выводим сообщение
        if (!empty($diff)) {
            return new JsonResponse(['error' => 'Missing or incorrect field'], 400);
        }

        //записываем в массив значения
        $newUser = [
            'first_name' => $jsonRequest['firstname'],
            'last_name' => $jsonRequest['lastname'],
            'nickname' => $jsonRequest['nickname'],
            'email' => $jsonRequest['email'],
            'age' => $jsonRequest['age'],
            'password' => $jsonRequest['password']
        ];

        //проверяем заполнены ли все поля
        foreach ($newUser as $item) {
            if (trim($item) == NULL) {
                return new JsonResponse(['error' => 'Invalid or empty value: required all fields to fill'], 400);
            }
        }

        //проверяем введено ли число в поле age
        if (!is_numeric($newUser['age'])) {
            return new JsonResponse(['error' => 'Age must be an integer'], 400);
        }

        //проверяем валидность введенного email
        if (!filter_var($newUser['email'], FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'invalid email'], 400);
        }

        //получаем подключение к бд
        $dbh = $this->getDoctrine()->getConnection();

        /*выполняем запрос: если будет возвращена ошибка уникальности ключа - выводим сообщение,
        * иначе выводим id успешной записи
        */
        try {
//            $dbh->executeStatement('INSERT INTO users SET first_name = :first_name,
//            last_name = :last_name, nickname = :nickname, email = :email,
//             age = :age, password = :password', $newUser);
            $dbh->executeStatement("INSERT INTO users(first_name,
            last_name, nickname, email, age, password) VALUES('{$jsonRequest['firstname']}', 
            '{$jsonRequest['lastname']}', '{$jsonRequest['nickname']}', '{$jsonRequest['email']}', 
            '{$jsonRequest['age']}', '{$jsonRequest['password']}')");
        } catch (UniqueConstraintViolationException $exception) {
            return new JsonResponse(['error' => 'email or nickname already exists'], 400);
        }

        return new JsonResponse(['id' => $dbh->lastInsertId()], 201);
    }

    /**
     * @Route("/userget", methods={"GET"})
     */
    public function userGet(Request $request)
    {
        //проверяем авторизацию - наличие валидного хэша
        $hasAccess = $this->checkAccess($request);
        if (!$hasAccess) {
            return new JsonResponse(['error' => 'Unauthorized. Access denied'], 401);
        }

        $jsonRequest = json_decode($request->getContent(), true);

        //выбирает из запроса параметр type и возвращает его значение (nickname при запросе ?type=nickname)
        $type = isset($jsonRequest['type']) ? $jsonRequest['type'] : null;

        //определяем допустимые типы запросов
        $acceptedRequests = [
            'nickname',
            'email'
        ];

        /*
        * если тип запроса не поддерживается (не emails или nickname), вернуть сообщение об ошибке;
        * если тип запроса совпадает, идем дальше
        */
        if (!in_array($type, $acceptedRequests)) {
            return new JsonResponse(['error' => 'Unsupported or missing request type'], 400);
        }

        //если параметра list нет в запросе или он пуст, вернуть сообщение об ошибке
        $requestedValues = isset($jsonRequest['list']) ? $jsonRequest['list'] : false;
        if (empty($requestedValues)) {
            return new JsonResponse(['error' => 'Request is empty or missing'], 400);
        }

        $dbh = $this->getDoctrine()->getConnection();

        /*
         * ищем совпадение в базе по каждому значению из списка list соответствующего типу
         * и извлекаем имя, фамилию, ник и почту
        */
        foreach ($requestedValues as $value) {
            $list = $dbh->fetchAllAssociative("SELECT first_name, last_name, nickname, email FROM users WHERE 
            {$type} ='{$value}'");
            if (count($list)) {
                $result[] = array_shift($list);
            }
        }

        //возвращаем тип данных и список пользователей
        return new JsonResponse(['type' => $type, 'list' => $result], 200);
    }

    /**
     * @Route("/user", methods={"PATCH"})
     */
    public function loginAction(Request $request)
    {
        //проверяем и записываем в переменные значеия полей name и pass
        if (!trim($request->get('name'))) {
            return new JsonResponse(['error' => 'Nickname field data invalid or is empty'], 401);
        }
        $name = $request->get('name');

        if (!trim($request->get('pass'))) {
            return new JsonResponse(['error' => 'Password field data invalid or is empty'], 401);
        }
        $pass = $request->get('pass');

        //извлекаем из базы соответствующие записи
        $dbh = $this->getDoctrine()->getConnection();
        $list = $dbh->fetchAllAssociative("SELECT id FROM users WHERE nickname='{$name}' 
        AND password='{$pass}'");

        //если логин и пароль в базе существуют, то в $list будет не пустой и сгенерируется хэш
        if (count($list) > 0) {
            $random = random_bytes(10);
            $hash = password_hash($random, PASSWORD_BCRYPT);

            //заисываем (новый) хэш в бвзу и сообщаем, что пользователь авторизировался
            $dbh->executeStatement("UPDATE users SET hash = '{$hash}' WHERE nickname='{$name}'");
            return new JsonResponse(['status' => 'Login accepted'], 200);
        }

        //если логин и/или пароль не существуют, возвращаем ошибку
        return new JsonResponse(['error' => 'Nickname or password not found'], 401);
    }

    private function checkAccess($request)
    {
        //если в заголовках отсутствует заголовок AccessHash, возвращаем ошибку
        if (empty($request->headers->get('AccessHash'))) {
            return false;
        }

        $dbh = $this->getDoctrine()->getConnection();
        $list = $dbh->fetchAllAssociative("SELECT id FROM users WHERE 
    hash='{$request->headers->get('AccessHash')}'");

        //если такой хэш не найден в базе (нет результатов запроса), возвращаем ошибку доступа
        return !(count($list) == 0);
    }
}
