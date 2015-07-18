<?php

/*
 * This file is part of the simple cms project for Yii2
 *
 * (c) Schallschlucker Agency Paul Kerspe - project homepage <https://github.com/pkerspe/yii2-simple-cms>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace schallschlucker\simplecms\controllers\backend;

use Yii;
use schallschlucker\simplecms\models\CmsDocument;
use schallschlucker\simplecms\models\CmsDocumentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CmsDocumentController implements the CRUD actions for CmsDocument model.
 * @menuLabel CMS Administration
 * @menuIcon <i class="fa fa-files-o"></i>
 */
class CmsDocumentController extends Controller {
	public function behaviors() {
		return [ 
			'verbs' => [ 
				'class' => VerbFilter::className (),
				'actions' => [ 
					'delete' => [ 
						'post' 
					] 
				] 
			] 
		];
	}
	
	/**
	 * Lists all CmsDocument models.
	 *
	 * @return mixed 
	 * @menuLabel list all cms douments
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendRead
	 */
	public function actionIndex() {
		$searchModel = new CmsDocumentSearch ();
		$dataProvider = $searchModel->search ( Yii::$app->request->queryParams );
		
		return $this->render ( 'index', [ 
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider 
		] );
	}
	
	/**
	 * Displays a single CmsDocument model.
	 *
	 * @param integer $id        	
	 * @return mixed 
	 * @menuLabel __HIDDEN__
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendRead
	 */
	public function actionView($id) {
		return $this->render ( 'view', [ 
			'model' => $this->findModel ( $id ) 
		] );
	}
	
	/**
	 * Creates a new CmsDocument model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 *
	 * @return mixed 
	 * @menuLabel crate new cms document
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendWrite
	 */
	public function actionCreate() {
		$model = new CmsDocument ();
		
		if ($model->load ( Yii::$app->request->post () ) && $model->save ()) {
			return $this->redirect ( [ 
				'view',
				'id' => $model->id 
			] );
		} else {
			return $this->render ( 'create', [ 
				'model' => $model 
			] );
		}
	}
	
	/**
	 * Updates an existing CmsDocument model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 *
	 * @param integer $id        	
	 * @return mixed 
	 * @menuLabel __HIDDEN__
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendWrite
	 */
	public function actionUpdate($id) {
		$model = $this->findModel ( $id );
		
		if ($model->load ( Yii::$app->request->post () ) && $model->save ()) {
			return $this->redirect ( [ 
				'view',
				'id' => $model->id 
			] );
		} else {
			return $this->render ( 'update', [ 
				'model' => $model 
			] );
		}
	}
	
	/**
	 * Deletes an existing CmsDocument model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 *
	 * @param integer $id        	
	 * @return mixed 
	 * @menuLabel __HIDDEN__
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendWrite
	 */
	public function actionDelete($id) {
		$this->findModel ( $id )->delete ();
		
		return $this->redirect ( [ 
			'index' 
		] );
	}
	
	/**
	 * Finds the CmsDocument model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param integer $id        	
	 * @return CmsDocument the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id) {
		if (($model = CmsDocument::findOne ( $id )) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException ( 'The requested page does not exist.' );
		}
	}
}
