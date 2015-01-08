<?php

namespace AppBundle\Menu\Voter;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CatalogMenuVoter implements VoterInterface {    
    
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Checks whether an item is current.
     *
     * If the voter is not able to determine a result,
     * it should return null to let other voters do the job.
     *
     * @param ItemInterface $item
     * @return boolean|null
     */
    public function matchItem(ItemInterface $item)
    {
        
        $uri = $item->getUri();
        $query = parse_url($uri, PHP_URL_QUERY);
        $queryArgs = array();
        parse_str($query, $queryArgs);
        
        if (preg_match("/catalog_.*/", $this->container->get('request')->get('_route'))) {  
                                    
            foreach (array('manufacturerId', 'categoryId', 'productTypeId') as $key) {        
                if (array_key_exists($key, $queryArgs) && $queryArgs[$key] == $this->container->get('request')->get($key)) {
                    return true;                    
                }
                
                if (sizeof($queryArgs) == 0 && $this->container->get('request')->get($key) !== null) {
                    return false;
                }
            }
            
        }

        return null;
    }
    
}