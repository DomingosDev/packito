<?php
class SampleController{

	/*
		Simple Crud operations using RedBeans and Rest api:

		Consider the model:
		Sample
			id
			name
			description


	*/


	public static function _updateSample(
		// Create and update a Sample
		$_url = array('PUT'=>'sample', 'POST'=>'sample'),
		$sample
		){
		$DBSample = R::dispense('sample');
		$DBSample->import($sample);
		R::store($sample);
		echo json_encode($DBSample->export());
	}


	// Remove a Sample
	public static function _deleteSample(
		$_url=array('DELETE'=>'sample/:id'),
		$id
		){
		$sample = R::load('sample', $id);
		R::trash($sample);
	}

	public static function _getSample(
		$_url = array(
			'GET' => 'sample/:id'
		),
		$id
	){
		$sample = R::load('sample', $id);
		if(!$sample->id){
			System::halt(404);
		}
		echo json_encode($sample->export());
	}

	public static function _getAllSamples(
		$_url = array( 'GET' => 'samples')
	){

		echo json_encode(R::exportAll( R::findAll('sample') ));
	}
}
?>