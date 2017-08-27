<?php

use MP\Model\Mp\ProductComment;

require_once 'MpUserServiceBase.php';

class ProductCommentService extends MpUserServiceBase
{

    public function delete()
    {
        $id = $this->_app->request()->get( ProductComment::PRODUCT_COMMENT_ID );
        $productComment = new ProductComment([ProductComment::PRODUCT_COMMENT_ID => $id]);
        $productComment->delete();

    }


}