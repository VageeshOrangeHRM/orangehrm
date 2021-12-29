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

namespace OrangeHRM\Time\Service;

use OrangeHRM\Entity\ProjectActivity;
use OrangeHRM\Time\Dao\ProjectActivityDao;
use OrangeHRM\Time\Dao\ProjectDao;
use OrangeHRM\Time\Exception\ProjectServiceException;

class ProjectService
{
    /**
     * @var ProjectDao|null
     */
    private ?ProjectDao $projectDao = null;

    /**
     * @var ProjectActivityDao|null
     */
    protected ?ProjectActivityDao $projectActivityDao = null;

    /**
     * @return ProjectDao
     */
    public function getProjectDao(): ProjectDao
    {
        if (is_null($this->projectDao)) {
            $this->projectDao = new ProjectDao();
        }
        return $this->projectDao;
    }

    /**
     * @return ProjectActivityDao
     */
    public function getProjectActivityDao(): ProjectActivityDao
    {
        if (!$this->projectActivityDao instanceof ProjectActivityDao) {
            $this->projectActivityDao = new ProjectActivityDao();
        }
        return $this->projectActivityDao;
    }

    /**
     * @param int $toProjectId
     * @param int $fromProjectId
     * @param int[] $fromProjectActivityIds
     * @return void
     * @throws ProjectServiceException
     */
    public function validateProjectActivityName(int $toProjectId, int $fromProjectId, array $fromProjectActivityIds)
    {
        $fromProjectActivities = $this->getProjectActivityDao()
            ->getProjectActivitiesByProjectIds($fromProjectActivityIds);
        $commonActivityList = [];
        foreach ($fromProjectActivities as $fromProjectActivity) {
            $activities = $this->getProjectActivityDao()
                ->getDuplicatedActivityIds($fromProjectActivity->getProject()->getId(), $toProjectId); //common activity
            $commonActivityList = $this->getProjectActivityAsMap($activities);
        }

        foreach ($fromProjectActivityIds as $fromProjectActivityId){
            if (is_null($this->getProjectActivityDao()->getProjectActivityById($fromProjectActivityId))) {
                throw ProjectServiceException::projectActivityNotFound();
            }
        }

        foreach ($fromProjectActivityIds as $fromProjectActivityId){
            if (is_null($this->getProjectActivityDao()->getProjectActivityByProjectIdAndProjectActivityId($fromProjectId, $fromProjectActivityId))) {
                throw ProjectServiceException::projectActivityNotBelongsToGivenProjectId();
            }
        }

        $fromProjectActivityList = $this->getProjectActivityAsMap($fromProjectActivities);
        foreach ($fromProjectActivityList as $fromProjectActivity) {
            $name = $fromProjectActivity['name'];
            if (!isset($commonActivityList[$name]) === false) {
                throw ProjectServiceException::duplicateProjectActivityNameFound();
            }
        }
    }

    /**
     * @param ProjectActivity[] $projectActivities
     * @return array
     */
    public function getProjectActivityAsMap(array $projectActivities): array
    {
        $projectActivityList = [];
        foreach ($projectActivities as $value) {
            $projectActivityList[$value->getName()] = [
                "id" => $value->getId(),
                "name" => $value->getName(),
            ];
        }
        return $projectActivityList;
    }
}
