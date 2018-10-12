<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Category;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends AbstractController
{
    /**
     * list of all categories
     * @return json
     */
    public function list()
    {
        $catagories = array();
        $repository = $this->getDoctrine()->getRepository(Category::class);
        $categories_data = $repository->findAll();

        if (isset($categories_data) && count($categories_data) > 0)
        {
            foreach ($categories_data as $cate)
            {
                $catagories[] = array(
                    'name' => $cate->getName(),
                );
            }
        }
        else
        {
            return $this->json(array('status' => 'fail', 'message' => 'No category found.'));    
        }

        return $this->json($catagories);
    }
}
