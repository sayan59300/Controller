<?php

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Itval\core\Classes\Session;
use Itval\core\Factories\EventFactory;

/**
 * Class Controller controlleur principal
 * @author nicolas_buffart<concepteur-developpeur@nicolas-buffart.fr>
 */
class Controller
{

    private $request;
    private $vars = [];
    public $emitter;

    /**
     * Controller constructor.
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        extract($this->vars);
        $this->request = $request;
        $this->id = uniqid();
        date_default_timezone_set("Europe/Berlin");
        $this->time = date("Y-m-d H:i:s");
        $this->emitter = EventFactory::getInstance();
    }

    /**
     * getter
     * @param string $key
     * @return mixed
     */
    protected function get(string $key)
    {
        return $this->$key;
    }

    /**
     * ajoute une entrée dans le tableau $vars
     * @param string $key
     * @param mixed $value
     */
    protected function set(string $key, $value = null)
    {
        if (is_array($key)) {
            $this->vars += $key;
        } else {
            $this->vars[$key] = $value;
        }
    }

    /**
     * retourne la vue demandée
     * @param string $viewName
     * @return Response
     */
    protected function render(string $viewName): Response
    {
        $response = new Response();
        extract($this->vars);
        $view = ROOT . DS . 'views' . DS . $this->request->controller . DS . $viewName . '.phtml';
        if (file_exists(ROOT . DS . 'views' . DS . $this->request->controller . DS . $viewName . '.phtml')) {
            ob_start();
            require $view;
            $contentForBody = ob_get_clean();
            ob_start();
            require ROOT . DS . 'views' . DS . 'templates' . DS . 'base.phtml';
            $body = ob_get_clean();
            $response->withStatus(200);
            $response->getBody()->write($body);
            return $response;
        } else {
            return error404();
        }
    }

    /**
     * initialise un token aléatoire et indique son heure de création dans 2 variables de session
     * attribut la valeur du token dans une variable de même nom qui est utilisée dans la vue
     * retourne le token au besoin pour une autre utilisation
     * @return string
     */
    protected function setToken(): string
    {
        $token = generateToken();
        $this->set('token', $token);
        return $token;
    }

    /**
     * retourne la liste des erreurs dans une chaine de caractères
     * @param array $values
     * @return string
     */
    protected function formattedErrors(array $values): string
    {
        $erreurs = '';
        foreach ($values as $value) {
            $erreurs .= $value . '<br>';
        }
        return $erreurs;
    }

    /**
     * génère les variables de session pour le formulaire d'inscription
     * @param array $values
     */
    protected function setValuesSession(array $values)
    {
        foreach ($values as $key => $value) {
            Session::set($key, $value);
        }
    }

    /**
     * unset les variables de session du formulaire d'inscription
     * @param array $args
     */
    protected function resetValuesSession(array $args)
    {
        $keys = array_keys($args);
        foreach ($keys as $key) {
            Session::delete($key);
        }
    }
}