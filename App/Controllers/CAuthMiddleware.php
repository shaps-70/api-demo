<?php

namespace App\Controllers;

use App\Classes\Logg;

class CAuthMiddleware {

	// private $container;
	private $db;

	public function __construct($container) {
		// $this->container = $container;
		$this->db = $container->get('db');
	}

	public function __invoke($request, $response, $next) {

		$token = $request->getHeaderLine('Authorization');

		if($token) {
			$token = str_replace('Bearer ', '', $token);

			try {
				$stmt = $this->db->prepare("SELECT * FROM ApiUsers WHERE token = :token");
				$stmt->bindValue(':token', $token);
				$stmt->execute();
				$result = $stmt->fetch();

				$dtCreated = new \DateTime($result['tokenCreated']);
				$dtNow = new \DateTime();

				if ($stmt->rowCount() && ($dtNow->getTimestamp() - $dtCreated->getTimestamp()) <= 3000) {

					$tokenCreated = date("Y-m-d H:i:s");
					$params = ['tokenCreated' => $tokenCreated, 'id' => $result['id']];
					$stmt = $this->db->prepare("UPDATE ApiUsers SET tokenCreated = :tokenCreated WHERE id = :id");
					$stmt->execute($params);

					$request = $request->withAttribute('hcode', $result['hospitalCode']);
					$request = $request->withAttribute('adminaccess', $result['admin']);
					return $next($request, $response);
				}
			}
			catch (\PDOException $e) {
				Logg::error('(check token): ' . $e->getMessage());
			}
		}

		return $response->withJson([
			'message' => 'Authentication required',
			'error' => [
				"message" => "403 Forbidden",
				"code" => 403
			]
		]);

	}

}