<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Framework\FoundationBundle\Controller;

class MainController extends Controller
{

    public function indexAction($color)
    {
        $player = $this->container->getLichessGeneratorService()->createGameForPlayer($color);

        // When munin pings the website, don't save the new game
        if(0 !== strncmp($this->getRequest()->server->get('HTTP_USER_AGENT'), 'Wget/', 5)) {
            $this->container->getLichessSynchronizerService()->setAlive($player);
            $this->container->getLichessPersistenceService()->save($player->getGame());
        }

        return $this->render('LichessBundle:Main:index', array(
            'player' => $player,
            'parameters' => $this->container->getParameterBag()->all(),
            'isOpponentConnected' => false
        ));
    }

    public function aboutAction()
    {
        return $this->render('LichessBundle:Main:about');
    }

    public function notFoundAction()
    {
        $response = $this->render('LichessBundle:Main:notFound');
        $response->setStatusCode(404);
        return $response;
    }
}
