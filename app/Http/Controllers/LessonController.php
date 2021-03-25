<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends \TCG\Voyager\Http\Controllers\VoyagerBaseController
{

    protected $response = [
        'status' => 200,
        'success' => true,
        'message' => '',
        'data' => null
    ];

    public function getLessonCommentsApi($lessonId)
    {
    	/*
    	$lesson = Lesson::with(
    		[
    			'comments:id,parent_id,comment,comment_for,is_approved,user_id',
    			'comments.author:id,name'
    		]
    	)->where('id',$lessonId)->first();
		*/

    	$comment = Comment::select('id', 'parent_id', 'comment_for', 'is_approved', 'user_id')
    	->with('author:id,name')
    	->where('parent_id', $lessonId)
    	->where('comment_for', 'lesson')
    	->where('is_approved', true)
    	->get();


    	if (!$comment) {
    		$this->response['status'] = 404;
    		$this->response['success'] = false;
    		$this->response['message'] = 'Not found';
        	return response()->json($this->response, $this->response['status']);
    	}

    	$this->response['status'] = 200;
		$this->response['success'] = true;
		$this->response['message'] = 'comments';
		$this->response['data'] = $comment;
    	return response()->json($this->response, $this->response['status']);
    }


    public function addLessonCommentsApi(Request $request, $lessonId)
    {
    	$user = auth()->user();
    	if (!$user) { return false; }

    	$comment = Comment::Create([
    		'parent_id' => $lessonId,
    		'comment' => $request->comment,
    		'comment_for' => 'lesson',
    		'is_approved' => false,
    		'user_id' => $user->id
    	]);

    	if (!$comment) {
    		$this->response['status'] = 404;
    		$this->response['success'] = false;
    		$this->response['message'] = 'Not found';
        	return response()->json($this->response, $this->response['status']);
    	}

    	$this->response['status'] = 200;
		$this->response['success'] = true;
		$this->response['message'] = 'Comment added to lesson successfuly.';
    	return response()->json($this->response, $this->response['status']);
    }
}
