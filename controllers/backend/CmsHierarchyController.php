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
use schallschlucker\simplecms\models\CmsHierarchyItem;
use schallschlucker\simplecms\models\CmsHierarchyItemSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CmsHierarchyController implements the CRUD actions for CmsHierarchyItem model.
 * @menuLabel CMS Administration
 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
 */
class CmsHierarchyController extends Controller {
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
	 * Lists all CmsHierarchyItem models.
	 * 
	 * @menuLabel __HIDDEN__
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendRead
	 * 
	 * @return mixed rendered view
	 */
	public function actionIndex() {
		$searchModel = new CmsHierarchyItemSearch ();
		$dataProvider = $searchModel->search ( Yii::$app->request->queryParams );
		
		return $this->render ( 'index', [ 
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider 
		] );
	}
	
	/**
	 * Displays a single CmsHierarchyItem model.
	 * 
	 * @menuLabel __HIDDEN__
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendRead
	 * 
	 * @param integer id the id of the hierarchy item to display  	
	 * @return mixed
	 */
	public function actionView($id) {
		return $this->render ( 'view', [ 
			'model' => $this->findModel ( $id ) 
		] );
	}
	
	/**
	 * Creates a new CmsHierarchyItem model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 *
	 * @menuLabel Create new hierarchy item
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendWrite
	 * 
	 * @return mixed
	 */
	public function actionCreate() {
		$model = new CmsHierarchyItem ();
		
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
	 * Updates an existing CmsHierarchyItem model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 *
	 * @menuLabel __HIDDEN__
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendWrite
	 * 
	 * @param integer $id the id of the hierarchy item to update   	
	 * @return \yii\web\Response|string
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
	 * Deletes the CmsHierarchyItem, specified by the id parameter.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * 
	 * @menuLabel __HIDDEN__
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendWrite
	 *
	 * @param integer $id the id of the hierarchy item to delete 	
	 * @return \yii\web\Response|string
	 */
	public function actionDelete($id) {
		$this->findModel ( $id )->delete ();
		
		return $this->redirect ( [ 
			'index' 
		] );
	}
	
	/**
	 * Finds the CmsHierarchyItem model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param integer $id        	
	 * @return CmsHierarchyItem the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id) {
		if (($model = CmsHierarchyItem::findOne ( $id )) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException ( 'The requested page does not exist.' );
		}
	}
}