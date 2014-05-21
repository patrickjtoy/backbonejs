<?php

	require_once 'vendor/autoload.php';

	$app = new \Slim\Slim();
	$redis = new Predis\Client();

	function getUserList($redis) {
		$users = array();
		$keys = $redis->keys('*');

		foreach ($keys as $key) {
			$user = array('id' => $key, 'firstname' => $redis->hget($key, 'firstname'), 'lastname' => $redis->hget($key, 'lastname'), 'age' => $redis->hget($key, 'age'));
			array_push($users, $user);
		}

		return $users;
	}

	function getUser($id, $redis) {
		$user = array('id' => $id, 'firstname' => $redis->hget($id, 'firstname'), 'lastname' => $redis->hget($id, 'lastname'), 'age' => $redis->hget($id, 'age'));

		return $user;
	}

	function addUser($user, $redis) {
		$id = uniqid();
		return $redis->hmset($id, ['firstname' => $user->{'firstname'}, 'lastname' => $user->{'lastname'}, 'age' => $user->{'age'}]);
	}

	function updateUser($user, $redis) {
		$id = $user->{'id'};
		return $redis->hmset($id, ['firstname' => $user->{'firstname'}, 'lastname' => $user->{'lastname'}, 'age' => $user->{'age'}]);
	}

	function deleteUser($id, $redis) {
		return $redis->del($id);
	}

	$app->get('/', function() {
		require_once 'home.php';
	});
	$app->get('/users', function() use($redis) {
		$response = getUserList($redis);
		exit(json_encode($response));
	});
	$app->get('/users/:id', function($id) use($redis) {
		$response = getUser($id, $redis);
		exit(json_encode($response));
	});
	$app->post('/users', function() use($app, $redis) {
		$data = json_decode($app->request->getBody());
		$response = addUser($data, $redis);
		exit(json_encode($response));
	});
	$app->put('/users/:id', function($id) use($app, $redis) {
		$data = json_decode($app->request->getBody());
		$response = updateUser($data, $redis);
		exit(json_encode($response));
	});
	$app->delete('/users/:id', function($id) use($redis) {
		$response = deleteUser($id, $redis);
		exit(json_encode($response));
	});
	$app->run();