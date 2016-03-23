<?php

/*
 * Copyright (C) 2015 Jens
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Controller;
class RouteController extends BaseController{
    public static function findTabConnections(\Model\Tab $fromTab, \Model\Tab $toTab) {
        $fromSystems = $fromTab->returnMapSystems();
        $toSystems = $toTab->returnMapSystems();
        $connections = [];
        if (empty($fromSystems)) {
            return [];
        }
        foreach ($fromSystems as $fromSystem) {
            if ($fromSystem->isKSpace()===false) {
                //don't check for routes of w-space systems
                continue;
            }
            $route = new \Lib\Route($fromSystem->solarSystemID());
            $shortestJump = -1;
            $shortestSystem=null;
            if (empty($toSystems)) {
                break;
            }
            foreach ($toSystems as $toSystem) {
                $jumps = $route->jumps($toSystem->solarSystemID());
                if ($shortestJump>-1 AND $shortestJump>$jumps) {
                    $shortestJump = $jumps;
                    $shortestSystem = $toSystem;
                }
            }
            if ($shortestJump>-1) {
                $connections[] = [
                    'fromSolarSystem'=>$fromSystem->solarSystemName(),
                    'fromLabel'=>$fromSystem->mapLabel(),
                    'toSolarSystemName'=>$shortestSystem->solarSystemName(),                    
                    'toLabel'=>$shortestSystem->mapLabel(),                    
                    'jumps'=>$shortestJump
                        ];
            }
        }
        
        return $shortestJump;
    }
}
