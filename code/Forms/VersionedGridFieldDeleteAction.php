<?php


class VersionedGridFieldDeleteAction extends GridFieldDeleteAction
{
	/**
	 * Handle the actions and apply any changes to the GridField
	 *
	 * @param GridField $gridField
	 * @param string $actionName
	 * @param mixed $arguments
	 * @param array $data - form data
	 * @return void
	 */
	public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
		if($actionName == 'deleterecord') {
			$item = $gridField->getList()->byID($arguments['RecordID']);
			if(!$item) {
				return;
			}
			if($actionName == 'deleterecord' && !$item->canDelete()) {
				throw new ValidationException(_t('GridFieldAction_Delete.DeletePermissionsFailure',"No delete permissions"),0);
			}
			if($actionName == 'deleterecord') {
				$item->doDelete();
			}
		} 
	}	
}