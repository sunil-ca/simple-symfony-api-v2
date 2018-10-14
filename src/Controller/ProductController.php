<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;

class ProductController extends AbstractController
{
    /**
     * add a new product 
     * end-point: /product/add
     * @param object $request Request Object
     * @param SessionInterface $session
     * @return json
     */
    public function add(Request $request, SessionInterface $session, LoggerInterface $logger) 
    {
        $is_valid_user = $session->get('valid_user');

        if ($is_valid_user)
        {
            $is_ajax = $request->isXmlHttpRequest(); // is it an Ajax request?

            if ($is_ajax)
            {
                $name = $request->request->get('name');
                $price = $request->request->get('price');
                $category = $request->request->get('category');
                $sku = $request->request->get('sku');
                $quantity = $request->request->get('quantity');

                if (!empty($name) && !empty($sku) && !empty($price) && !empty($quantity) && !empty($category))
                {
                    $product = new Product();
                    $product->setName($name);
                    $product->setSku($sku);
                    $product->setPrice($price);
                    $product->setQuantity($quantity);
                    $product->setCategory($category);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($product);
                    $em->flush();
                    return $this->json(array('status' => 'success', 'message' => 'Product added successfully.'));
                }
                else
                {
                    return $this->json(array('status' => 'fail', 'message' => 'Product data missing.'));
                }
            }
            else
            {
                return $this->json(array('status' => 'fail', 'message' => 'Something went wrong. Please check your request.'));
            }
        }
        else
        {
            //log to log-file
            $logger->error("Login Failed for a user");

            return $this->json(array('status' => 'fail', 'message' => 'Not a valid user'));
        }
    }

    /**
     * list of all products 
     * end-point: /product/list
     * @return json
     */
    public function list()
    {
        $products =  array();
        $repository = $this->getDoctrine()->getRepository(Product::class);
        $data = $repository->findAll();

        if (isset($data) && count($data) > 0)
        {
            foreach ($data as $p)
            {
                $products[] = array(
                    'name' => $p->getName(),
                    'price' => $p->getPrice(),
                    'category' => $p->getCategory(),
                    'sku' => $p->getSku(),
                    'quantity' => $p->getQuantity()
                );
            }
        }
        else
        {
            return $this->json(array('status' => 'fail', 'message' => 'No product found.'));
        }

        return $this->json($products);
    }

    /**
     * update an product
     * end-point: /product/update
     * @param int $id id of the product
     * @param object $request object
     * @param SessionInterface $session
     * @return json
     */
    public function update($id, Request $request, SessionInterface $session, LoggerInterface $logger)
    {
        if (!is_numeric($id)) 
        {
            return $this->json(array('status' => 'fail', 'message' => 'product id is not valid'));
        }

        $is_valid_user = $session->get('valid_user');

        if ($is_valid_user)
        {
            $is_ajax = $request->isXmlHttpRequest();
            
            if ($is_ajax)
            {
                $name = $request->request->get('name');
                $price = $request->request->get('price');
                $category = $request->request->get('category');
                $sku = $request->request->get('sku');
                $quantity = $request->request->get('quantity');
                $entityManager = $this->getDoctrine()->getManager();
                $product = $entityManager->getRepository(Product::class)->find($id);

                if ($name || $price || $category || $sku || $quantity)
                {
                    if (!$product) 
                    {
                        return $this->json(array('status' => 'fail', 'message' => 'product not found'));
                    }

                    $product->setName($name);
                    $product->setPrice($price);
                    $product->setCategory($category);
                    $product->setSku($sku);
                    $product->setQuantity($quantity);

                    $entityManager->persist($product);
                    $entityManager->flush();

                    return $this->json(array('status' => 'success', 'message' => 'product updated successfully'));
                }
                else
                {
                    return $this->json(array('status' => 'fail', 'message' => 'product data not missing.'));
                }
            }
            else
            {
                return $this->json(array('status' => 'fail', 'message' => 'Something went wrong. Please check your request.'));
            }
        }
        else
        {
            //log to log-file
            $logger->error("Login Failed for a user");

            return $this->json(array('status' => 'fail', 'message' => 'Not a valid user'));
        }

    }

    /**
     * Get a single product
     * end-point: /product/show/{id}
     * @param int $id id of product
     * @return json
     */
    public function show($id)
    {
        $product = array();

        if (!is_numeric($id)) 
        {
            return $this->json(array('status' => 'fail', 'message' => 'product id is not valid'));
        }

        $p_data = $this->getDoctrine()->getRepository(Product::class)->find($id);
    
        if (!$p_data) 
        {
            return $this->json(array('status' => 'fail', 'message' => 'no product found.'));
        }

        $product = array(
            'name' => $p_data->getName(),
            'price' => $p_data->getPrice(),
            'category' => $p_data->getCategory(),
            'sku' => $p_data->getSku(),
            'quantity' => $p_data->getQuantity()
        );

        return $this->json($product);
    }

    /**
     * delete a product
     * end-point: /product/delete/{id}
     * @param int $id id of product
     * @param SessionInterface $session
     * @return json
     */
    public function delete($id, SessionInterface $session, LoggerInterface $logger)
    {
        if (!is_numeric($id)) 
        {
            return $this->json(array('status' => 'fail', 'message' => 'product id is not valid'));
        }

        $is_valid_user = $session->get('valid_user');

        if ($is_valid_user)
        {
            $product = $this->getDoctrine()->getRepository(Product::class)->find($id);
        
            if (!$product) 
            {
                return $this->json(array('status' => 'fail', 'message' => 'no product found.'));
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($product);
            $entityManager->flush();
            return $this->json(array('status' => 'success', 'message' => 'product deleted.'));
        }
        else
        {
            //log to log-file
            $logger->error("Login Failed for a user");

            return $this->json(array('status' => 'fail', 'message' => 'Not a valid user'));
        }
    }


}
