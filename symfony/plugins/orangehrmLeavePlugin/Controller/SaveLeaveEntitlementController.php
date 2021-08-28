<?php
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 */

namespace OrangeHRM\Leave\Controller;

use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Admin\Service\LocationService;
use OrangeHRM\Admin\Service\CompanyStructureService;
use OrangeHRM\Core\Controller\AbstractVueController;

class SaveLeaveEntitlementController extends AbstractVueController
{
    protected ?CompanyStructureService $companyStructureService = null;
    protected ?LocationService $locationService = null;

    /**
     * @return LocationService
     */
    protected function getLocationService(): LocationService
    {
        if (!$this->locationService instanceof LocationService) {
            $this->locationService = new LocationService();
        }
        return $this->locationService;
    }

    /**
     * @return CompanyStructureService
     */
    protected function getCompanyStructureService(): CompanyStructureService
    {
        if (!$this->companyStructureService instanceof CompanyStructureService) {
            $this->companyStructureService = new CompanyStructureService();
        }
        return $this->companyStructureService;
    }

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $id = $request->get('id');
        if ($id) {
            $component = new Component('leave-edit-entitlement');
            $component->addProp(new Prop('entitlement-id', Prop::TYPE_NUMBER, $id));
        } else {
            $component = new Component('leave-add-entitlement');
        }

        $subunits = $this->getCompanyStructureService()->getSubunitArray();
        $subunits = array_map(
            function ($item) {
                return [
                    "id" => $item['id'],
                    "label" => $item['label'],
                    "_indent" => $item['indent'],
                ];
            },
            $subunits
        );
        $component->addProp(new Prop('subunits', Prop::TYPE_ARRAY, $subunits));

        $locations = $this->getLocationService()->getAccessibleLocationsArray();
        $component->addProp(new Prop('locations', Prop::TYPE_ARRAY, $locations));

        $this->setComponent($component);
    }
}