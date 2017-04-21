<?php
use Respect\Validation\Validator as DataValidator;
DataValidator::with('CustomValidations', true);

/**
 * @api {post} /article/get-all Retrieve all articles.
 *
 * @apiName Get all
 *
 * @apiGroup article
 *
 * @apiDescription This path retrieves all the articles.
 *
 * @apiPermission any or user
 *
 * @apiUse NO_PERMISSION
 * 
 * @apiSuccess {Object} data
 *
 */

class GetAllArticlesController extends Controller {
    const PATH = '/get-all';
    const METHOD = 'POST';

    public function validations() {
        return [
            'permission' => (Controller::isUserSystemEnabled()) ? 'user' : 'any',
            'requestData' => []
        ];
    }

    public function handler() {
        $topics = Topic::getAll();
        $topicsArray = [];
        
        foreach($topics as $topic) {
            $topicsArray[] = $topic->toArray();
        }
        
        Response::respondSuccess($topicsArray);
    }
}