<?php
/**
 * @author federico
 * @since 24/12/18 10:47
 */

namespace App\Controller;

use App\Entity\BlogPost;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/blog")
 */
class BlogController extends AbstractController
{
    /**
     * @Route("/{page}", name="blog_list", defaults={"page":"1"}, requirements={"page"="\d+"})
     */
    public function list($page, Request $request)
    {
        $limit = $request->get('limit', 10);

        $repository = $this->getDoctrine()->getRepository(BlogPost::class);
        $items = $repository->findAll();

        return $this->json(
            [
            'page' => $page,
            'limit'=> $limit,
            'data' => array_map(function (BlogPost $item) {
                return $this->generateUrl('blog_by_slug', ['slug'=>$item->getSlug()]);
            },$items)
            ]);
    }

    /**
     * @Route("/post/{id}", name="blog_by_id", requirements={"id"="\d+"}, methods={"GET"})
     * @ParamConverter("post", class="App:BlogPost")
     */
    public function post($post)
    {
        //It's the same than doing $this->getDoctrine()->getRepository(BlogPost::class)->find($id)
        return $this->json($post);
    }

    /**
     * @Route("/post/{slug}", name="blog_by_slug", methods={"GET"})
     * The annotation below is not required when $post is typehinted with BlogPost and route parameter name matches
     * any field on the BlogPost entity
     * @ParamConverter("post", class="App:BlogPost", options={"mapping":{"slug":"slug"}})
     */
    public function postBySlug($post)
    {
        //It's the same than doing $this->getDoctrine()->getRepository(BlogPost::class)->findBy(['slug' => $slug])
        return $this->json($post);
    }

    /**
     * @Route("/add", name="blog_add", methods={"POST"})
     */
    public function add(Request $request)
    {
//            $normalizer = new ObjectNormalizer();
//            $encoder = new JsonEncoder();
//
//            $serializer = new Serializer(array($normalizer), array($encoder));

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');

        $blogPost = $serializer->deserialize($request->getContent(), BlogPost::class, 'json');

        $em = $this->getDoctrine()->getManager();
        $em->persist($blogPost);
        $em->flush();

        return $this->json($blogPost);
    }

/**
 * @Route("/post/{id}", name="blog_delete", methods={"DELETE"})
 */
    public function delete(BlogPost $post)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}