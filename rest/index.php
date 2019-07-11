<?php
require '../vendor/autoload.php';
use \Firebase\JWT\JWT;

Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=db_faruk_a_2019-06-19','root',''));

Flight::route('POST /v1/login', function () {
    $request = Flight::request();
    $user = [
        'username' => $request->data->username,
        'pass' => $request->data->pass,
    ];

    $get = "SELECT * FROM users WHERE username = :username";

    $stmt = Flight::db()->prepare($get);
    $stmt->execute(["username" => $user['username']]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(!isset($users[0])){
        Flight::halt(404, "User not found");
    } else {
        if($users[0]['password'] != $user['pass']) Flight::halt(403, "Wrong password");
        $token = [
            "exp" => time() + 36000,
            "data" => $res,
        ];
        $jwt = JWT::encode($token, "SECRET", "HS256");
        Flight::json(["token" => $jwt]);
    }
});



Flight::route('GET /v1/count', function(){
    $get = "SELECT * FROM bills";
    $paid = 0;
    $unpaid = 0;

    $stmt = Flight::db()->prepare($get);
    $stmt->execute();
    $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($bills as $bill ) {
        if($bill['status'] == 'Paid'){
            $paid++;
        } else $unpaid++;
    }
    Flight::json(['paid' => $paid, 'unpaid' => $unpaid]);
});

Flight::route('GET /v1/bills', function(){
    $get = "SELECT * FROM bills";

    $stmt = Flight::db()->prepare($get);
    $stmt->execute();
    $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
    Flight::json($bills);
});


Flight::route('POST /v1/bills', function(){
    $request = Flight::request()->data->getData();
    $insert = "INSERT INTO bills (type, date, amount, status) VALUES(:type, :date, :amount, :status)";
    $stmt= Flight::db()->prepare($insert);
    $stmt->execute($request);
});

Flight::route('DELETE /v1/bills/@id', function($id){
    $request = Flight::request()->data->getData();
    $request['id'] = $id;

    $delete = "DELETE FROM bills WHERE id = :id";

    $stmt= Flight::db()->prepare($delete);
    $stmt->execute(["id" => $id]);
});

Flight::start();
?>
