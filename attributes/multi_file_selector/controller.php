<?php  

namespace Concrete\Package\MultiFileSelectorAttribute\Attribute\MultiFileSelector;

use \Concrete\Core\File\Type\Type as FileType;

class Controller extends \Concrete\Core\Attribute\Controller  {

 	public function getRawValue() {
		$db = \Database::connection();
		$value = $db->fetchColumn("select value from atMultiFileSelector where avID = ?", array($this->getAttributeValueID()));
		return trim($value);
	}
	
	public function getValue() {
		$fileIDs = $this->getFileIDsArrayValue();
			
		foreach($fileIDs as $fID) {
			$file = \File::getByID($fID);
			if ($file) {
				$files[] = $file;
			}
		}

		return $files;
	}

	public function getFileIDsArrayValue() {
		$value = $this->getRawValue();
		$fileIDs = array();

		if ($value) {
			$fileIDs = explode(',', $value);
		}

		return $fileIDs;
	}

 	public function form() {
		$this->load();
		$values =  $this->getValue();

		$v = \View::getInstance();
		$v->requireAsset('core/file-manager');

		$id = \Core::make('helper/validation/identifier')->getString(8);

		echo '<ul class="list-group multi-file-list" id="'.$id.'">';
		if (!empty($values)) {
			foreach ($values as $file) {
				$thumb = $file->getListingThumbnailImage();
				echo '<li class="list-group-item">' . $thumb . ' ' .$file->getTitle() .'<a><i class="pull-right fa fa-minus-circle"></i></a><input type="hidden" name="' . $this->field('value') . '[]" value="' . $file->getFileID() . '" /></li>';
			}
		}
		echo '</ul>';

		switch($this->akType) {
			case 'file':
				$filetype = FileType::T_UNKNOWN;
				$label = t('Choose Files');
				break;
			case 'image':
				$filetype = FileType::T_IMAGE;
				$label = t('Choose Images');
				break;
			case 'video':
				$filetype = FileType::T_VIDEO;
				$label = t('Choose Video Files');
				break;
			case 'text':
				$filetype = FileType::T_TEXT;
				$label = t('Choose Text Files');
				break;
			case 'audio':
				$filetype = FileType::T_AUDIO;
				$label = t('Choose Audio Files');
				break;
			case 'doc':
				$filetype = FileType::T_DOCUMENT;
				$label = t('Choose Documents');
				break;
			case 'app':
				$filetype = FileType::T_APPLICATION;
				$label = t('Choose Application Files');
				break;
			default:
				$filetype = '';
				$label = t('Choose Files');

		}

		$hide = '';

		if ($this->akMaxItems > 0 && count($values) >= $this->akMaxItems) {
			$hide = 'hidden';
		}

		$filter =  ", filters : []";
		$check = 'if(true){';

		if ($filetype) {
			$filter =  ", filters : [{ field : 'type', type : '"  . $filetype . "' }]";
			$check = 'if (file.genericTypeText == "' . FileType::getGenericTypeText($filetype) . '") {';
		}

		echo "<div href=\"#\" id=\"". $id ."_launch\" data-max-items=\"".$this->akMaxItems."\" data-launch=\"file-manager\" class=\"ccm-file-selector ". $hide ."\"><div class=\"ccm-file-selector-choose-new\">".$label."</div></div>
		<script type=\"text/javascript\">
		$(function() {
			$('#" . $id ."_launch').on('click', function(e) {
				e.preventDefault();

				var options = {
      				multipleSelection: true " . $filter . "
 				}

				ConcreteFileManager.launchDialog(function (data) {
					ConcreteFileManager.getFileDetails(data.fID, function(r) {
						var maxItems = 	$('#" . $id ."_launch').data('max-items');
						var currentItems = $('#" . $id ." li').size();

						if (maxItems > 0 && r.files.length > (maxItems - currentItems)) {
							var toomanymessage = '".t('Please select a maximum of %1$s files to add, %2$s of %3$s files are currently selected') . "';" . '
							var remaining = maxItems - currentItems;
							toomanymessage = toomanymessage.replace(\'%1$s\',remaining);
							toomanymessage = toomanymessage.replace(\'%2$s\',currentItems);
							toomanymessage = toomanymessage.replace(\'%3$s\',maxItems);
							alert(toomanymessage);
							' . "
						} else {
							for(var i in r.files) {
								var file = r.files[i];
								" .$check. "
									$('#" . $id ."').append('<li class=\"list-group-item\">'+ file.resultsThumbnailImg +' ' +  file.title +'<a><i class=\"pull-right fa fa-minus-circle\"></i></a><input type=\"hidden\" name=\"" . $this->field('value') . "[]\" value=\"' + file.fID + '\" /></li>');
									$('#ccm-panel-detail-page-attributes').animate({scrollTop: '+=83px'}, 0);

									var currentItems = $('#" . $id ." li').size();

									if (maxItems > 0 && currentItems >= maxItems) {
										$('#" . $id ."_launch').addClass('hidden');
									}
								} else {
									alert('".t('Please select only %s file types', t($filetype ? FileType::getGenericTypeText($filetype) : 'file'))."');
								}
							}
						}
					});
				},options);
			});

			$('#" . $id ."').sortable({ axis: 'y'});

			$('#" . $id ."').on('click', 'a', function(){
				$(this).parent().remove();

				var maxItems = 	$('#" . $id ."_launch').data('max-items');

				if (maxItems > 0 && $('#" . $id ." li').size() < maxItems) {
					$('#" . $id ."_launch').removeClass('hidden');
				}
			});
		});
		</script>
		<style>
			.ccm-ui .multi-file-list {margin-bottom: 0};
			.multi-file-list li {cursor: move}
			.ccm-ui .multi-file-list li:last-child {margin-bottom: 10px;}
			.multi-file-list img {max-width: 60px!important; display: inline!important; margin-right: 10px;}
			.multi-file-list .fa {cursor: pointer}
			.multi-file-list a:hover {color: red}
		</style>

		";

	}

	protected function load()
	{
		$ak = $this->getAttributeKey();
		if (!is_object($ak)) {
			return false;
		}

		$db =  \Database::connection();
		$row = $db->query('select akType, akMaxItems from atMultiFileSelectorSettings where akID = ?', array($ak->getAttributeKeyID()));
		$row = $row->fetch();

		$this->akType = $row['akType'];
		$this->set('akType', $this->akType);

		$this->akMaxItems = $row['akMaxItems'];
		$this->set('akMaxItems', $this->akMaxItems);
	}

	public function type_form() {
		$this->load();
		$pageTypeList = \PageType::getList();
		$this->set('pageTypeList', $pageTypeList);
		$this->set('form', \Core::make('helper/form'));
		$this->set('page_selector', \Core::make('helper/form/page_selector'));
	}

 
	public function saveValue($value) {
		$db = \Database::connection();

		if (is_array($value)) {
			$value = implode(',',$value);
		}

		if (!$value) {
			$value = '';
		}

		$db->Replace('atMultiFileSelector', array('avID' => $this->getAttributeValueID(), 'value' => $value), 'avID', true);
	}

	public function saveKey($data)
	{
		$ak = $this->getAttributeKey();
		$db =\Database::connection();

		$akRestrictSingle = 0;
		if (isset($data['akRestrictSingle']) && $data['akRestrictSingle']) {
			$akRestrictSingle = 1;
		}

		$akType = $data['akType'];
		$akMaxItems = (int)$data['akMaxItems'];

		$db->Replace('atMultiFileSelectorSettings', array(
			'akID' => $ak->getAttributeKeyID(),
			'akMaxItems' => $akMaxItems,
			'akType' => $akType
		), array('akID'), true);
	}


	public function deleteKey() {
		parent::deleteKey();
		$db = \Database::connection();
		$arr = $this->attributeKey->getAttributeValueIDList();
		foreach($arr as $id) {
			$db->query('delete from atMultiFileSelector where avID = ?', array($id));
		}
	}
	
	public function saveForm($data) {
		$this->saveValue($data['value']);
	}
	
	public function deleteValue() {
		$db = \Database::connection();
		$db->query('delete from atMultiFileSelector where avID = ?', array($this->getAttributeValueID()));
	}
	
}
