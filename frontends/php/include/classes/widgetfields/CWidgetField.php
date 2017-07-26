<?php
/*
** Zabbix
** Copyright (C) 2001-2017 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

class CWidgetField {

	protected	$name;
	protected	$label;
	protected	$value;
	protected	$default;
	protected	$save_type;
	protected	$action;
	private		$validation_rules = [];
	private		$ex_validation_rules = [];

	/**
	 * Create widget field (general)
	 *
	 * @param string $name   Field name in form.
	 * @param string $label  Label for the field in form.
	 */
	public function __construct($name, $label = null) {
		$this->name = $name;
		$this->label = $label;
		$this->value = null;
		$this->setSaveType(ZBX_WIDGET_FIELD_TYPE_STR);
	}

	public function setValue($value) {
		$this->value = $value;

		return $this;
	}

	public function setDefault($value) {
		$this->default = $value;

		return $this;
	}

	/**
	 * Set JS code that will be called on field change.
	 *
	 * @param string $action  JS function to call on field change.
	 *
	 * @return $this
	 */
	public function setAction($action) {
		$this->action = $action;

		return $this;
	}

	protected function setSaveType($save_type) {
		switch ($save_type) {
			case ZBX_WIDGET_FIELD_TYPE_INT32:
				$this->validation_rules = ['type' => API_INT32];
				break;

			case ZBX_WIDGET_FIELD_TYPE_STR:
				// TODO VM: (?) should we have define for this?
				$this->validation_rules = ['type' => API_STRING_UTF8, 'length' => 255];
				break;

			case ZBX_WIDGET_FIELD_TYPE_GROUP:
			case ZBX_WIDGET_FIELD_TYPE_HOST:
				$this->validation_rules = ['type' => API_IDS];
				break;

			case ZBX_WIDGET_FIELD_TYPE_ITEM:
			case ZBX_WIDGET_FIELD_TYPE_MAP:
				$this->validation_rules = ['type' => API_ID];
				break;

			default:
				exit(_('Internal error'));
		}

		$this->save_type = $save_type;

		return $this;
	}

	protected function setValidationRules(array $validation_rules) {
		$this->validation_rules = $validation_rules;
	}

	protected function setExValidationRules(array $ex_validation_rules) {
		$this->ex_validation_rules = $ex_validation_rules;
	}

	/**
	 * Get field value. If no value is set, will return default value.
	 *
	 * @return mixed
	 */
	public function getValue() {
		return ($this->value === null) ? $this->default : $this->value;
	}

	public function getLabel() {
		return $this->label;
	}

	public function getName() {
		return $this->name;
	}

	public function getAction() {
		return $this->action;
	}

	public function getSaveType() {
		return $this->save_type;
	}

	public function validate() {
		$errors = [];

		$validation_rules = $this->validation_rules + $this->ex_validation_rules;
		$value = $this->getValue();
		$label = ($this->label === null) ? $this->name : $this->label;

		if (!CApiInputValidator::validate($validation_rules, $value, $label, $error)) {
			$errors[] = $error;
		}

		return $errors;
	}

	/**
	 * Prepares array entry for widget field, ready to be passed to CDashboard API functions
	 *
	 * @return array  An array of widget fields ready for saving in API.
	 */
	public function toApi() {
		$value = $this->getValue();
		$widget_fields = [];

		if ($value !== null && $value !== $this->default) {
			$widget_field = [
				'type' => $this->save_type,
				'name' => $this->name
			];

			if (is_array($value)) {
				foreach ($value as $val) {
					$widget_field['value'] = $val;
					$widget_fields[] = $widget_field;
				}
			}
			else {
				$widget_field['value'] = $value;
				$widget_fields[] = $widget_field;
			}
		}

		return $widget_fields;
	}
}