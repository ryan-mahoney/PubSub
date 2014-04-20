<?php
return function ($context, $membership, $post) {
	try {
		$membership->userJoinOrExtend($post->membership['user_id'], $post->membership['membership_id']);
	} catch (\Exception $e) {
		$this->post->errorFieldSet ($context['formMarker'], 'Error becoming member: ' . $e->getMessage());
		return false;
	}
};
