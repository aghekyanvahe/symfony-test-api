<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 1/13/20
 * Time: 4:12 PM
 */

namespace App\Controller;


use App\Entity\User;
use App\Service\RequestHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
/**
 * @Route("/api/user")
 */
class RestController extends AbstractController {

    /**
     * @Route("/", methods={"POST"})
     */
    public function create(Request $request, RequestHelper $helper, LoggerInterface $logger)
    {

        try {

            $em = $this->getDoctrine()->getManager();

            $user = new User();


            $firtname = $request->get('firstname');
            $lastname = $request->get('lastname');

            $validator = $helper->validateFields([
                'firstname' => $firtname,
                'lastname' => $lastname
            ]);


            if(count($validator)) {
                return $helper->response('error', ['error'=> $validator]);
            }

            $user->setFirstname($firtname);
            $user->setLastname($lastname);

            $em->persist($user);
            $em->flush();

            // log database modification
            $logger->info("User created with body ".json_encode([
                    'id' => $user->getId(),
                    'firstname' => $user->getFirstname(),
                    'lastname' => $user->getFirstname()
            ]));

            return $helper->response('success', [
                'message' => 'User Created!'
            ]);

        } catch (\Exception $exception) {
            return $helper->response('error', [
                'message' => $exception->getMessage()
            ]);

        }
    }

    /*
     * VALID SORTING
     * ?sort={"firstname":"ASC","lastname":"DESC"}
     * ?sort={"firstname":"ASC","lastname":"ASC"}
     * ?sort={"firstname":"ASC"}
     * ?sort={"firstname":"DESC"}
     * ?sort={"lastname":"DESC"}
     */

    /**
     * @Route("/", methods={"GET"})
     */
    public function findAll(Request $request,RequestHelper $helper)
    {


        try {
            $userRepository = $this->getDoctrine()->getRepository(User::Class);
            $usersQuery = $userRepository->createQueryBuilder("u");


            $sort = $helper->checkSorting( $request->get('sort'));

            if(count($sort)) {
                foreach ($sort as $item) {
                    $usersQuery->addOrderBy("u.".$item['key'], $item['dir']);
                }
            }

            $users = $usersQuery
                ->getQuery()
                ->getArrayResult();

            return $helper->response('success', [
                'result' => $users
            ]);

        } catch (\Exception $exception) {

            return $helper->response('error', [
                'message' => $exception->getMessage()
            ]);
        }
    }

    /**
     * @Route("/{id}", methods={"GET"})
     */
    public function findOne($id, RequestHelper $helper)
    {

        try {
            $userRepository = $this->getDoctrine()->getRepository(User::Class);
            $user =  $userRepository->createQueryBuilder("u")
                ->where('u.id = :id')
                ->setParameter('id',$id)
                ->getQuery()
                ->getArrayResult();

            if($user) {
                return $helper->response('success', [
                    'result' => $user
                ]);
            }

            return $helper->response('error', [
                'message' => 'User not found'
            ]);

        } catch (\Exception $exception) {
            return $helper->response('error', [
                'message' => $exception->getMessage()
            ]);
        }
    }

    /**
     * @Route("/{id}", methods={"POST"})
     */
    public function update(Request $request, LoggerInterface $logger,RequestHelper $helper, $id)
    {

        try {
            $userRepository = $this->getDoctrine()->getRepository(User::Class);
            $user = $userRepository->find($id);

            if($user) {

                $firstname = $request->get('firstname');
                $lastname = $request->get('lastname');

                if($firstname) {
                    $user->setFirstname($firstname);
                }

                if($lastname) {
                    $user->setLastname($lastname);
                }


                $data = [
                    'firstname' => $firstname,
                    'lastname' => $lastname
                ];

                $validator = $helper->validateFields($data);


                if(count($validator)) {
                    return $helper->response('error', ['error'=> $validator]);
                }

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();


                $user =  $userRepository->createQueryBuilder("u")
                    ->where('u.id = :id')
                    ->setParameter('id',$id)
                    ->getQuery()
                    ->getArrayResult();



                // log database modification
                $logger->info("User with ID ".$id." updated -> ".json_encode($data)." !!");

                return $helper->response('success', [
                    'result' => $user
                ]);
            }

            return $helper->response('error', [
                'message' => 'User not found'
            ]);

        } catch (\Exception $exception) {
            return $helper->response('error', [
                'message' => $exception->getMessage()
            ]);
        }

    }


    /**
     * @Route("/{id}",  methods={"DELETE"})
     */
    public function deleteOneAction($id,LoggerInterface $logger,RequestHelper $helper)
    {

        try {
            $userRepository = $this->getDoctrine()->getRepository(User::Class);
            $user =  $userRepository->find($id);

            if($user) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($user);
                $em->flush();



                $logger->info("User with ID ".$id." deleted!!");
                return $helper->response('success', [
                    'message' => 'Users deleted!'
                ]);

            }

            return $helper->response('error', [
                'message' => 'User not found'
            ]);

        } catch (\Exception $exception) {
            return $helper->response('error', [
                'message' => $exception->getMessage()
            ]);
        }
    }
}