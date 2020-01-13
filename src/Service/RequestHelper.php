<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 1/13/20
 * Time: 5:14 PM
 */

namespace App\Service;


use Symfony\Component\HttpFoundation\JsonResponse;

class RequestHelper
{

    function validateFields($toValidate) {

        $invalid = [];

        foreach ($toValidate as $key => $value) {
            if(!$value) {
                $invalid[$key] = "Field is required";
            }
        }

        return $invalid;
    }


    function response($status,  $data) {
        $key = array_key_first($data);

        return new JsonResponse([
            'status' => $status,
             "$key" => $data[$key]
        ]);
    }


    function checkSorting($sort) {
        $sort = json_decode($sort, true);
        $toSort = [];



        if($sort) {
            foreach ($sort as $key => $dir) {
                if(
                    ($key == 'firstname' || $key == 'lastname') &&
                    ($dir == 'ASC' || $dir == 'DESC')
                ) {
                    array_push($toSort, [
                        'key' => $key,
                        'dir' => $dir
                    ]);
                }
            }
        }

        return $toSort;
    }

}