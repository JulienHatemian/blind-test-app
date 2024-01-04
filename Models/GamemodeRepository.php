<?php
namespace Blindtest\Repository;

use Blindtest\Repository\MainRepository;
use PDO;

require_once(__DIR__ . '/../Models/MainRepository.php');
class GamemodeRepository
    extends MainRepository
{
    public function getAllGamemode():array
    {
        $req = "SELECT * FROM gamemode";
        $pdo = $this->getDatabase()->prepare($req);
        $pdo->execute();
        $data = $pdo->fetchAll(PDO::FETCH_ASSOC);
        $pdo->closeCursor();
        return $data;
    }

    public function getGamemodeById(int $id):array
    {
        $req = "SELECT * FROM gamemode WHERE idgamemode = :id";
        $pdo = $this->getDatabase()->prepare($req);
        $pdo->bindValue(':id', $id, PDO::PARAM_INT);
        $pdo->execute();
        $data = $pdo->fetchAll(PDO::FETCH_ASSOC);
        $pdo->closeCursor();
        return $data;
    }
}