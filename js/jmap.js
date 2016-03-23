

//---------------VARIABLES-------------------
var CON_ANCHOR = [[0.5, 0.5, 0, 1]];
var ENDPOINT_OPT = {
    anchor: CON_ANCHOR,
    endpoint: ["Dot", {radius: 4}],
    maxConnections: 100,
    uniqueEndpoint: true,
    deleteEndpointsOnDetach: false,
    isSource: true,
    isTarget: true,
    connector: "Straight",
    dragAllowedWhenFull: false,
    hoverPaintStyle: {outlineColor: "#888"},
    connectorStyle: {lineWidth: 8, strokeStyle: "#CCC", outlineColor: "#000", outlineWidth: 1}
};
var PaintStyle = {lineWidth: 8, strokeStyle: "#CCC", outlineColor: "#000", outlineWidth: 1};
var yelPaintStyle = {lineWidth: 8, strokeStyle: "#FFFF00", outlineColor: "#000", outlineWidth: 1};
var bluePaintStyle = {lineWidth: 8, strokeStyle: "#1A51E8", outlineColor: "#6D8FED", outlineWidth: 1};
var redPaintStyle = {lineWidth: 8, strokeStyle: "#C00", outlineColor: "#000", outlineWidth: 1};
var eolPaintStyle = {lineWidth: 8, strokeStyle: "#CCC", outlineColor: "#F0F", outlineWidth: 3};
var eolYelPaintStyle = {lineWidth: 8, strokeStyle: "#FFFF00", outlineColor: "#F0F", outlineWidth: 3};
var eolRedPaintStyle = {lineWidth: 8, strokeStyle: "#C00", outlineColor: "#F0F", outlineWidth: 3};
var eolBluePaintStyle = {lineWidth: 8, strokeStyle: "#1A51E8", outlineColor: "#F0F", outlineWidth: 3};

var vConstants = {//contains constant variables
    clientVersion: '3.0.5',
    loopTime: 3000,
    timeoutTime: 8000,
    mapSize: 4000,
    mapCenter: 2000,
    mapOffsetX: 120,
    mapOffsetY: 120
};
var vGlobals = {//contains all globally used variables    
    activeTabID: 1,
    pilotSolarSystemID: 0,
    characterName: '',
    dragging: false
};

//------------------OBJECTS------------

function jMessage(_action, _data) {
    this.action = _action;
    this.data = _data;
    if (typeof (_data)=='undefined') {
        this.data = new Array();
    }
}
function jWormhole(_json) {
    this.identifier = _json.identifier;
    this.targetClass = _json.targetClass;
    this.sigSize = _json.sigSize;
    this.stableTime = _json.stableTime;
    this.stableMass = _json.stableMass;
    this.jumpMass = _json.jumpMass;
}
function jEffect(_json) {
    this.class = _json.solarSystemClass;
    this.effect = _json.solarSystemEffect;
    this.display = _json.display;
    this.isPositive = _json.isPositive == 1 ? true : false;
    this.value = parseInt(_json.value) > 0 ? Math.round(_json.value * 100) : Math.round(100 * (_json.value - 1));
}

function jPilot(_json) {
    this.characterName = _json.characterName;
    this.shipTypeName = _json.shipTypeName;
    this.solarSystemID = _json.solarSystemID;
}

function jSignature(_json) {
    this.sig = _json.sig;
    this.solarSystemID = _json.solarSystemID;
    this.groupID = _json.groupID;
    this.isAnom = _json.isAnom==false||_json.isAnom=='false'?false:true;
    this.type = (typeof (_json.type)!='undefined')?_json.type:'';
    this.detail = (typeof (_json.detail)!='undefined')?_json.detail:'';
    this.timeCreated = _json.timeCreated;
    this.isOld = typeof (_json.isOld)!='undefined'?_json.isOld:false;     
}
;
function jIntel(_json) {
    this.solarSystemID = _json.solarSystemID;
    this.id = _json.id;
    this.type = _json.type;
    this.value = _json.value;
    this.eveID = _json.eveID;
    this.groupID = _json.groupID;
}

function jPOI(_json) {    
    this.solarSystemID = (typeof (_json.sourceSolarSystemID) != 'undefined') ? _json.sourceSolarSystemID : 0;
    this.poiSolarSystemName = _json.solarSystemName;
    this.poiSolarSystemID = _json.solarSystemID;
    this.distance = (typeof (_json.distance) != 'undefined') ? _json.distance : -1;
    this.description = (typeof (_json.description) != 'undefined') ? _json.description : -1;
    this.type = _json.type;
    this.jumps = _json.jumps;
    this.jumpsSafe = _json.jumpsSafe;
    this.id = _json.id;
    this.groupID = _json.groupID;
    this.stationID = _json.stationID;
}

function jMapSystem(_json) {
    this.tabID = _json.tabID;
    this.solarSystemID = _json.solarSystemID;
    this.solarSystemName = _json.solarSystemName;
    this.label = _json.label;
    this.solarSystemClass = _json.solarSystemClass;
    this.solarSystemEffect = _json.solarSystemEffect;
    this.security = parseInt(_json.security);
    this.mapX = parseInt(_json.mapX);
    this.mapY = parseInt(_json.mapY);
    this.regionName = _json.regionName;
    this.static1 = _json.static1;
    this.static2 = _json.static2;
    this.static3 = _json.static3;
    this.home = _json.home==true?true:false;
    this.rallypoint = _json.rallypoint==true?true:false;
    this.locked = _json.locked==true?true:false;
    this.dragged = false;
    this.divID = 's' + this.solarSystemID + 't' + this.tabID;
    this.solarSystemNameShort = this.solarSystemName; //shortened name for display in map
    if (this.solarSystemNameShort.length > 8) {
        this.solarSystemNameShort = this.solarSystemName.substring(0, 8) + '..';
    }
    this.effectArray = jEffectList.getEffectArray(_json.solarSystemClass, _json.solarSystemEffect);



}
;
jMapSystem.prototype = {
    print: function () {
        var tabDivID = 't' + this.tabID;
        var mapOutput = '';
        var self = this;

        mapOutput += '<div id="' + this.divID + '" class="mapsystem">';
        mapOutput += '<div id="label' + this.divID + '" class="systemlabel ' + this.solarSystemEffect + '"> ' + this.label + '</div>';
        mapOutput += '<div id="name' + this.divID + '" class="systemname">' + this.solarSystemNameShort + '</div>';
        mapOutput += '<div class="systembody">';
        mapOutput += '<div id="pilots' + this.divID + '" value="' + this.solarSystemID + '" class="pilotnumber"><b>Pilots: </b>0</div>';
        if (!this.isKSpace()) {
            var static1label = getClassLabel(jWormholeList.getWormhole(this.static1).targetClass);
            var static2label = getClassLabel(jWormholeList.getWormhole(this.static2).targetClass);

            mapOutput += '<div class="statics"><b>Statics:</b>';
            mapOutput += '<font class="' + static1label + '">' + static1label + '</font>';
            mapOutput += '<font class="' + static2label + '">' + static2label + '</font></div>';
            mapOutput += '<div class="tools"> ';
            mapOutput += '<div class="tool"><img src="img/radarblack.png"  onClick="jWindow.showSigWindow(' + this.solarSystemID + ',' + this.tabID + ')"></div>';
            mapOutput += '</div>';
        } else {
            mapOutput += '<div class="tools"> ';
            mapOutput += '<div class="tool"><img src="img/targetblack.png"  onClick="CCPEVE.setDestination(' + this.solarSystemID + ')"></div>';
            mapOutput += '<div class="tool"><img src="img/waypointblack.png"  onClick="CCPEVE.addWaypoint(' + this.solarSystemID + ')"></div>';
            mapOutput += '<div class="tool">';
            mapOutput += '<a href="http://evemaps.dotlan.net/map/' + this.regionName.replace(/\s/g, '_') + '/' + this.solarSystemName.replace(/\s/g, '_') + '#npc_delta" target=blank><img src="img/dotlan2.png"></a>';
            mapOutput += '</div>';
            mapOutput += '<div class="tool" onClick="CCPEVE.showMap(' + this.solarSystemID + ')"><img src="img/map.png"></div>';
            mapOutput += '</div>';
        }
        mapOutput += '</div></div>'; //systembody, mapsystem
        $('#map' + tabDivID).append(mapOutput);

        //set position
        $('#' + this.divID).css({top: (vConstants.mapCenter + this.mapY), left: (vConstants.mapCenter + this.mapX)});

        //add jPlumb Endpoint
        this.endpoint = jsPlumb.addEndpoint(this.divID, ENDPOINT_OPT);

        //make draggable
        $('#' + this.divID).multidraggable({
            grid: [31, 23],
            containment: 'parent'
        });
        //bind drag
        $('#' + this.divID).bind("drag", function () {
            if (vGlobals.dragging === false) {
                vGlobals.dragging = true;
            }
            self.dragged = true;
            ConnectionRepaint(self.endpoint, self.divID);
        });
        //bind mouseup (dragging stops now)
        $('#' + this.divID).mouseup(function () {
            if (vGlobals.dragging === true) {
                vGlobals.dragging = false;
                //dragging stops -> push movement to server
                var mapTab = jMap.returnOpenTab(self.tabID);
                jUI.updateDraggedSystems(mapTab.returnDraggedSystems());
            }

        });
        //bind double click
        $('#' + this.divID).dblclick(function () {
            $(".selected").removeClass("selected");
            $(".ui-multidraggable").removeClass("ui-multidraggable");
            self.massSelect();
        });
        //bind single click
        $('#' + this.divID).click(function () {
            $(".selected").removeClass("selected");
            $(".ui-multidraggable").removeClass("ui-multidraggable");
            jSystemBox.set(self);
        });
        $('#' + this.divID).bind("contextmenu", function (event) {
            jContext.showSystemContext(self, event);
            event.preventDefault();
            event.stopPropagation();
        });

        //bind pilot mouseover
        $('#pilots' + this.divID).bind('mouseenter', function () {
            var systemPilots = $.grep(jMap.Pilots, function (e) {
                return e.solarSystemID == self.solarSystemID;
            });
            var output = '';
            $.each(systemPilots, function (index, pilot) {
                output += pilot.characterName + ' (' + pilot.shipTypeName + ')<br>';
            });
            $('#mouseover').html(output);
            if (systemPilots.length > 0) {
                $('#mouseover').show();
            }
        });
        $('#pilots' + this.divID).bind('mouseleave', function () {
            $('#mouseover').hide();
        });
        $('#'+this.divID).bind('mouseenter',function(){
             jMap.findConnectedTab(self.solarSystemID,self.tabID);
         });
         $('#'+this.divID).bind('mouseleave',function(){
             $('.tabglow').removeClass('tabglow');
         });
        //bind pilot edit window
        $('#label' + this.divID).click(function (event) {
            jContext.showLabelContext(self, event);
        });
        if (this.solarSystemClass == 9) {
            $('#' + this.divID).addClass('nullsec');
        } else
        if (this.solarSystemClass == 7) {
            $('#' + this.divID).addClass('highsec');
        } else
        if (this.solarSystemClass == 8) {
            $('#' + this.divID).addClass('lowsec');
        }

        this.updateBorder();
    },
    move: function (_mapX, _mapY) {
        this.mapX = parseInt(_mapX);
        this.mapY = parseInt(_mapY);
        //set position
        $('#' + this.divID).css({top: (vConstants.mapCenter + this.mapY), left: (vConstants.mapCenter + this.mapX)});
        ConnectionRepaint(this.endpoint, this.divID);
    },
    massSelect: function () {
        if (this.home === false) {
            $('#' + this.divID).addClass("selected");
            $('#' + this.divID).addClass("ui-multidraggable");
            var systems = this.getConnectedSystems();
            $.each(systems, function (index, system) {
                system.massSelect();
            });
        }

    },
    getConnectedSystems: function () {
        var connections = jsPlumb.getConnections("*");
        var systems = new Array();
        var self = this;
        //look through all connections    
        $.each(connections, function (index, con) {
            if ((con.tabID == self.tabID) && (con.sourceMapSystem.solarSystemID == self.solarSystemID)) {
                systems.push(con.targetMapSystem);
            }
        });
        return systems;
    },
    isKSpace: function () {
        //systems that aren't high,low or null sec (7,8,9) are not kspace
        if ((this.solarSystemClass < 7) || (this.solarSystemClass > 9)) {
            return false;
        } else {
            return true;
        }
    },
    remove: function () {
        //remove connections
        jsPlumb.removeAllEndpoints(this.divID);
        //remove DOM object
        $('#' + this.divID).remove();
    },
    setRallypoint: function (_status) {
        this.rallypoint = _status;
    },
    setHome: function (_status) {
        this.home = _status;
    },
    setPin: function (_status) {
        this.locked = _status;
    },
    setLabel: function (_label) {
        this.label = _label;
        $('#label' + this.divID).html(_label);
    },
    isShattered: function () {
        if (this.solarSystemClass > 30 && this.solarSystemClass < 40) {
            return true;
        } else {
            return false;
        }
    },
    isFrigSystem: function () {
        if (this.solarSystemClass > 40) {
            return true;
        } else {
            return false;
        }
    },
    hasIntel: function () {
        var solarSystemID = this.solarSystemID;
        var intel = $.grep(jMap.Intels, function (e) {
            return e.solarSystemID == solarSystemID;
        });
        if (intel.length > 0) {
            return true;
        }
    },
    hasPOI: function () {
        var solarSystemID = this.solarSystemID;
        var pois = $.grep(jMap.POIs, function (e) {
            return (e.solarSystemID == solarSystemID) && e.type !== 'hub';
        });
        if (pois.length > 0) {
            console.log(this.solarSystemID);
            console.log(pois);
            return true;
        }
    },
    updateBorder: function () {
        $('#' + this.divID).removeClass('homeborder');
        $('#' + this.divID).removeClass('intelborder');
        $('#' + this.divID).removeClass('rallyborder');
        $('#' + this.divID).removeClass('poiborder');
        $('#' + this.divID).removeClass('shatteredborder');
        $('#' + this.divID).removeClass('frigsystemborder');
        if (this.rallypoint == true) {
            $('#' + this.divID).addClass('rallyborder');
        } else
        if (this.home == true) {
            $('#' + this.divID).addClass('homeborder');
        } else
        if (this.isShattered()) {
            $('#' + this.divID).addClass('shatteredborder');
        } else
        if (this.isFrigSystem()) {
            $('#' + this.divID).addClass('frigsystemborder');
        } else
        if (this.hasPOI()) {
            $('#' + this.divID).addClass('poiborder');
        } else
        if (this.hasIntel()) {
            $('#' + this.divID).addClass('intelborder');
        }
    },
    glow: function(_on) {
        if (_on===true) {
            $('#' + this.divID).addClass('glow');
        }
    }
};

function jMapTab(_json) {
    /**
     * @type Array.<jMapSystem> MapSystems
     */
    this.MapSystems = new Array();    
    this.Signatures = new Array();
    this.tabID = parseInt(_json.tabID);
    this.divID = 't' + this.tabID;
    this.tabName = _json.tabName;  
    this.groupID = _json.groupID;
    this.imgURL = _json.imgURL;
};

jMapTab.prototype = {
    print: function () {
        //print it
        var tabOutput = '';
        tabOutput += '<div id="map' + this.divID + '" class=map></div>';
        $('#mapborder').append(tabOutput);
        var self = this;
        //add zero marker
        var markerOutput = '<div id="zeromark' + this.divID + '" class="marker"><div class="text">0|0</div></div>';
        $("#map" + this.divID).append(markerOutput);
        $("#zeromark" + this.divID).css({position: 'relative', top: vConstants.mapCenter, left: vConstants.mapCenter + 17});

        //set map DIV size    
        $("#map" + this.divID).css({width: vConstants.mapSize, height: vConstants.mapSize, position: 'relative', top: vConstants.mapOffsetY - vConstants.mapCenter, left: vConstants.mapOffsetY - vConstants.mapCenter});

        //make map moveable    
        $("#map" + this.divID).draggable({containment: [-3500, -3500, 20, 130]});

        var mapHeaderOutput = '<div id="tabreg' + this.divID + '" class="tab tabinactive">';
        mapHeaderOutput += '<table class="tabtable" cellspacing=0 ><tr>';
        mapHeaderOutput += '<td width="28px" >'+(this.imgURL!=''?'<img src ="'+this.imgURL+'" align="middle" border="0" height="28" width="28">':'')+'</td>';
        mapHeaderOutput += '<td id="tabnamecell' + this.divID + '" width="107px" align="left">' + this.tabName + '</td></tr></table></div>';
        $('#mapheader').append(mapHeaderOutput);
        
        $('#tabreg'+this.divID).bind('mouseenter',function(){
            jMap.findConnectedSystems(self.tabID);
        });
        $('#tabreg'+this.divID).bind('mouseleave',function(){
            $('.glow').removeClass('glow');
        });
        
        //redraw the empty tab to put it to the end of the tabs
        jMap.drawEmptyTab();

        var tabObject = this;

        //bind left click event
        $('#tabreg' + this.divID).click(function (e) {
            //make active tab if left clicked
            tabObject.makeActive();
        });
        //bind right click event
        $('#tabreg' + this.divID).bind("contextmenu", function (event) {
            //show tab contextmenu                       
            jContext.showTabContext(tabObject, event);
            event.preventDefault();
            event.stopPropagation();
        });

    },
    makeActive: function () {
        $('.map').hide();
        $('#map' + this.divID).show();
        //remove tabactive class from old active tab
        $('.tabactive').each(function () {
            $(this).removeClass('tabactive');
            $(this).addClass('tabinactive');
        });
        //make this tab active
        $('#tabreg' + this.divID).removeClass('tabinactive');
        $('#tabreg' + this.divID).addClass('tabactive');
        vGlobals.activeTabID = this.tabID;
        jsPlumb.repaintEverything();
    },
    addSystem: function (_json) {        
        var newSystemKey = this.MapSystems.length;
        this.MapSystems[newSystemKey] = new jMapSystem(_json);
        this.MapSystems[newSystemKey].print();
    },
    deleteSystem: function (_json) {
        var mapsystem = this.returnMapSystem(_json.solarSystemID);
        //remove from MapSystems Array
        this.MapSystems = $.grep(this.MapSystems, function (e) {
            return e.solarSystemID == _json.solarSystemID;
        }, true);
        //remove MapSystem itself
        mapsystem.remove();
    },
    show: function () {
        $('#map' + this.divID).show();
        Resize();
    },
    hide: function () {
        $('#map' + this.divID).hide();
    },
    addConnection: function (_json) {
        var fromMapSystem = this.returnMapSystem(_json.fromSolarSystemID);
        var toMapSystem = this.returnMapSystem(_json.toSolarSystemID);
        if ((fromMapSystem === false) || (toMapSystem === false)) {
            console.log('connecting systems do not exist ');
            console.log(_json);
        } else {
            var connection = jsPlumb.connect({source: fromMapSystem.endpoint, target: toMapSystem.endpoint});
            connection.sourceMapSystem = fromMapSystem;
            connection.targetMapSystem = toMapSystem;
            connection.tabID = this.tabID;
            connection.type = _json.type;
            connection.massStage = parseInt(_json.massStage);
            connection.timeStage = parseInt(_json.timeStage);
            connection.massPassed = parseInt(_json.massPassed);
            connection.creationTime = parseInt(_json.creationTime);
            connection.eolTime = parseInt(_json.eolTime);
            connection.groupID = this.groupID;

            connection.updateColor();

            connection.bind('mouseenter', function (conn, event) {
                var output = 'open ' + MakeTime(conn.creationTime + jServer.clockOffset);
                if (connection.timeStage > 1) {
                    output += '<br>EoL ' + MakeTime(conn.eolTime + jServer.clockOffset);
                }
                var conmass = conn.massPassed;
                output += '<br>' + numberFormat(Math.round(conmass / 1000000)) + ' kt';
                $('#mouseover').html(output);
                $('#mouseover').show();
            });
            connection.bind('mouseexit', function () {
                $('#mouseover').hide();
            });
            connection.bind("contextmenu", function (connection, event) {
                $('#mouseover').hide();
                jContext.showConnectionContext(connection, event);
                event.preventDefault();
                event.stopPropagation();
            });

        }

    },
    returnDraggedSystems: function () {
        var movedSystems = new Array();
        $.each(this.MapSystems, function (index, system) {
            if (system.dragged) {
                movedSystems.push(system);
            }
        });
        return movedSystems;
    },
    moveSystem: function (_json) {
        var mapsystem = this.returnMapSystem(_json.solarSystemID);
        if (mapsystem !== false) {
            mapsystem.move(_json.mapX, _json.mapY);
        }
    },
    setSystemStatus: function (_json) {
        var mapsystem = this.returnMapSystem(_json.solarSystemID);
        if (mapsystem !== false) {
            if (_json.systemStatus === 'pin') {
                mapsystem.setPin(_json.state);
            } else
            if (_json.systemStatus === 'home') {
                mapsystem.setHome(_json.state);
            } else
            if (_json.systemStatus === 'rallypoint') {
                mapsystem.setRallypoint(_json.state);
            }
            mapsystem.updateBorder();
        }

    },
    setSystemLabel: function (_json) {
        var mapsystem = this.returnMapSystem(_json.solarSystemID);
        if (mapsystem !== false) {
            mapsystem.setLabel(_json.label);
        }
    },
    returnConnection: function (_sourceID, _targetID) {
        var connections = jsPlumb.getConnections("*");
        var tabID = this.tabID;
        var connectionArr = $.grep(connections, function (e) {
            return ((e.sourceMapSystem.solarSystemID == _sourceID && e.targetMapSystem.solarSystemID == _targetID && e.tabID == tabID) ||
                    (e.sourceMapSystem.solarSystemID == _targetID && e.targetMapSystem.solarSystemID == _sourceID && e.tabID == tabID))
        });
        if (connectionArr.length > 0) {
            return connectionArr[0];
        } else {
            return false;
        }
    },
    deleteConnection: function (_json) {
        var sourceID = _json.fromSolarSystemID;
        var targetID = _json.toSolarSystemID;
        var connection = this.returnConnection(sourceID, targetID);

        if (connection !== false) {
            jsPlumb.detach(connection);
        }

    },
    /**
     * 
     * @param {type} _solarSystemID solarSystemID
     * @returns jMapSystem
     */
    returnMapSystem: function (_solarSystemID) {
        var mapsystemArr = jQuery.grep(this.MapSystems, function (e) {
            return (e.solarSystemID == _solarSystemID)
        });
        if (mapsystemArr.length > 0) {
            return mapsystemArr[0];
        } else {
            return false;
        }

    },
    rename: function (_json) {
        this.tabName = _json.tabName;
        $('#tabnamecell' + this.divID).html(this.tabName);
    },
    addSignature: function (_json) {
        var signature = new jSignature(_json);
        var oldSignatureArr = $.grep(this.Signatures, function (e) {
            return (e.solarSystemID == signature.solarSystemID) && (e.sig == signature.sig);
        });
        if (oldSignatureArr.length > 0) {
            //we already have this signature ->update it
            oldSignatureArr[0].isAnom = signature.isAnom;
            oldSignatureArr[0].type = (signature.type !== '') ? signature.type : oldSignatureArr[0].type;
            oldSignatureArr[0].detail = (signature.detail !== '') ? signature.detail : oldSignatureArr[0].detail;
            oldSignatureArr[0].isOld = signature.isOld;
        } else {
            //it's new -> push it to the list
            this.Signatures.push(signature);
        }
    },
    glow: function(_on) {
        if (_on===true) {
            $('#tabreg' + this.divID).addClass('tabglow');
        }else{
            $('#tabreg' + this.divID).removeClass('tabglow');
        }
        
    }
};


jsPlumb.getDefaultConnectionType().prototype.updateColor = function () {
    //set connection color
    if (this.timeStage == 2) { //end of life                
        if (this.massStage == 1)
            this.setPaintStyle(eolPaintStyle);
        else //first mass
        if (this.massStage == 2)
            this.setPaintStyle(eolYelPaintStyle);
        else //second mass
        if (this.massStage == 3)
            this.setPaintStyle(eolRedPaintStyle);
        else// crit mass
        if (this.massStage == 4)
            this.setPaintStyle(eolBluePaintStyle); //frig hole
    } else {
        if (this.massStage == 1)
            this.setPaintStyle(PaintStyle);
        else //first mass
        if (this.massStage == 2)
            this.setPaintStyle(yelPaintStyle);
        else //second mass
        if (this.massStage == 3)
            this.setPaintStyle(redPaintStyle);
        else// crit mass
        if (this.massStage == 4)
            this.setPaintStyle(bluePaintStyle); //frig hole
    }
}


function jServerRequest(_url, _message, _complete) {
    this.url = _url;
    this.message = _message;
    this.complete = _complete;
}
function jSelectorOption(_value, _text) {
    this.value = _value;
    this.text = _text;
}


//-------------------------
var jSystemBox = {
    mapSystem: null,
    tab: null,
    set: function (_mapSystem) {
        this.mapSystem = _mapSystem;
        this.tab = jMap.returnOpenTab(vGlobals.activeTabID);
        this.drawSysInfo();
        this.drawIntel();
    },
    drawSysInfo: function () {
        if (this.mapSystem === null) {
            return false;
        }
        var systemOutput = '';
        var mapsystem = this.mapSystem;
        systemOutput += '<table>';
        systemOutput += '<tr><td><font class="name">' + mapsystem.solarSystemName + '</font>';
        systemOutput += '<font class="region">' + mapsystem.regionName + '</font></td></tr></table>';

        //if it's wormhole space show the statics, otherwise show POIs
        if (!mapsystem.isKSpace()) {
            var static1 = jWormholeList.getWormhole(mapsystem.static1);
            var static2 = jWormholeList.getWormhole(mapsystem.static2);
            var static3 = jWormholeList.getWormhole(mapsystem.static3);

            systemOutput += '<table>';
            systemOutput += '<tr>' +
                    '<td><font class="cl">' + getPrettyClassLabel(mapsystem.solarSystemClass) + '</font></td>' +
                    '<td><b id="effectinfo" class="' + mapsystem.solarSystemEffect + '">' + getPrettyEffect(mapsystem.solarSystemEffect) + '</b></td></tr>' +
                    '<tr><td><b>Static:</b></td>' +
                    '<td id="static1"><b><font class="' + getClassLabel(static1.targetClass) + '">' + mapsystem.static1 + ' ' + getPrettyClassLabel(static1.targetClass) + '</b></font></td></tr>' +
                    '<tr><td></td>' +
                    '<td id="static2"><b><font class="' + getClassLabel(static2.targetClass) + '">' + mapsystem.static2 + ' ' + getPrettyClassLabel(static2.targetClass) + '</b></font></td></tr>' +
                    '<tr><td></td>' +
                    '<td id="static3"><b><font class="' + getClassLabel(static3.targetClass) + '">' + mapsystem.static3 + ' ' + getPrettyClassLabel(static3.targetClass) + '</b></font></td></tr>';
            $('#systembox').html(systemOutput);

            if (mapsystem.static1 !== '') {
                $('#static1').mouseover(function () {
                    var mouseoverOutput = '<table>';
                    mouseoverOutput += '<tr><td>lifetime</td><td>' + static1.stableTime + 'h</td></tr>';
                    mouseoverOutput += '<tr><td>max jump mass</td><td>' + numberFormat(Math.round(static1.jumpMass / 1000000)) + 'kt</td></tr>';
                    mouseoverOutput += '<tr><td>total mass</td><td>' + numberFormat(Math.round(static1.stableMass / 1000000)) + 'kt</td></tr>';
                    mouseoverOutput += '<tr><td>sig size</td><td>' + static1.sigSize + '</td></tr>';
                    mouseoverOutput += '</table>';
                    $('#mouseover').html(mouseoverOutput);
                    $('#mouseover').show();
                });
                $('#static1').mouseout(function () {
                    $('#mouseover').hide();
                });
            }
            if (mapsystem.static2 !== '') {
                $('#static2').mouseover(function () {
                    var mouseoverOutput = '<table>';
                    mouseoverOutput += '<tr><td>lifetime</td><td>' + static2.stableTime + 'h</td></tr>';
                    mouseoverOutput += '<tr><td>max jump mass</td><td>' + numberFormat(Math.round(static2.jumpMass / 1000000)) + 'kt</td></tr>';
                    mouseoverOutput += '<tr><td>total mass</td><td>' + numberFormat(Math.round(static2.stableMass / 1000000)) + 'kt</td></tr>';
                    mouseoverOutput += '<tr><td>sig size</td><td>' + static2.sigSize + '</td></tr>';
                    mouseoverOutput += '</table>';
                    $('#mouseover').html(mouseoverOutput);
                    $('#mouseover').show();
                });
                $('#static2').mouseout(function () {
                    $('#mouseover').hide();
                });
            }
            if (mapsystem.static3 !== '') {
                $('#static3').mouseover(function () {
                    var mouseoverOutput = '<table>';
                    mouseoverOutput += '<tr><td>lifetime</td><td>' + static3.stableTime + 'h</td></tr>';
                    mouseoverOutput += '<tr><td>max jump mass</td><td>' + numberFormat(Math.round(static3.jumpMass / 1000000)) + 'kt</td></tr>';
                    mouseoverOutput += '<tr><td>total mass</td><td>' + numberFormat(Math.round(static3.stableMass / 1000000)) + 'kt</td></tr>';
                    mouseoverOutput += '<tr><td>sig size</td><td>' + static3.sigSize + '</td></tr>';
                    mouseoverOutput += '</table>';
                    $('#mouseover').html(mouseoverOutput);
                    $('#mouseover').show();
                });
                $('#static3').mouseout(function () {
                    $('#mouseover').hide();
                });
            }
            if (mapsystem.effect !== '') {
                $('#effectinfo').mouseover(function () {
                    var mouseoverOutput = '<table>';
                    var effects = jEffectList.getEffectArray(mapsystem.solarSystemClass, mapsystem.solarSystemEffect);
                    $.each(effects, function (index, effect) {
                        var judge = effect.isPositive ? 'positive' : 'negative';
                        var sign = effect.value > 0 ? '+' : '';
                        mouseoverOutput += '<tr><td class="' + judge + '">' + effect.display + '</td>' +
                                '<td style="text-align:right" class="' + judge + '">' + sign + effect.value + '%</td></tr>';
                    });
                    mouseoverOutput += '</table>';
                    $('#mouseover').html(mouseoverOutput);
                    $('#mouseover').show();
                });
                $('#effectinfo').mouseout(function () {
                    $('#mouseover').hide();
                });
            }

        } else {
            systemOutput += '<b><font class="' + getClassLabel(mapsystem.solarSystemClass) + '">' + getPrettyClassLabel(mapsystem.solarSystemClass) + '</font></b></td>';
            systemOutput += '<table><tr>';
            var pois = jMap.returnPOIs(mapsystem.solarSystemID);
            //add trade hubs
            $.each(pois, function (index, poi) {
                if (poi.type === 'hub') {
                    var destinationID = (poi.stationID!=0)? poi.stationID:poi.poiSolarSystemID;
                    systemOutput += '<td id="poi' + poi.id + '"><a href="#" onClick="CCPEVE.setDestination(' + destinationID + ')" class="evelink">' + poi.poiSolarSystemName + '(' + jUtils.returnJumpString(poi.jumpsSafe, poi.jumps) + ')</a></td>';
                }
            });
            systemOutput += '</tr></table><table><tr>';
            //add other POIs
            $.each(pois, function (index, poi) {
                if (poi.type !== 'hub') {
                    systemOutput += '<td><a href="#" poi-data="' + poi.description + ' (' + +parseFloat(poi.distance).toFixed(2) + 'Ly)" onClick="CCPEVE.setDestination(' + poi.poiSolarSystemID + ')" class="evelink poi">' + poi.poiSolarSystemName + '(' + jUtils.returnJumpString(poi.jumpsSafe, poi.jumps) + ')</a></td>';
                }
            });
            systemOutput += '</tr></table>';

            $('#systembox').html(systemOutput);
            $('.poi').bind('mouseenter', function () {
                var output = '';
                output += $(this).attr('poi-data');
                $('#mouseover').html(output);
                $('#mouseover').show();
            });
            $('.poi').bind('mouseleave', function () {
                $('#mouseover').hide();
            });
        }
    },
    drawIntel: function () {
        if (this.mapSystem === null) {
            return false;
        }
        var topboxOutput = "";
        var botboxOutput = "";
        var mapsystem = this.mapSystem;
        var tab = this.tab;
        $.each(jMap.Intels, function (index, intel) {
            if (intel.solarSystemID == mapsystem.solarSystemID && intel.groupID ==tab.groupID) {
                //check if there is a linkable id
                var output = '';
                if (intel.eveID != 0) {
                    var typeID = (intel.type == 'ally') ? 16159 : 2;
                    output += '<div class="intel"><a onClick="CCPEVE.showInfo(' + typeID + ',' + intel.eveID + ')">' + intel.value + '</a>';
                    output += '<a onClick=jUI.deleteIntel(' + intel.id + ','+intel.groupID+')>X</a></div>';
                } else {
                    output += '<div class="intel">' + intel.value;
                    output += '<a onClick=jUI.deleteIntel(' + intel.id + ','+intel.groupID+')>X</a></div>';
                }
                //decide which box to output it in
                if (intel.type === 'pos') {
                    botboxOutput += output;
                } else {
                    topboxOutput += output;
                }
            }
        });
        topboxOutput += '<div style="clear:both"></div>';
        botboxOutput += '<div style="clear:both"></div>';
        $('#topintelbox').html(topboxOutput);
        $('#botintelbox').html(botboxOutput);
    }
};

var jWindow = {
    sort: '',
    edit: '',
    solarSystemID: 0,
    tabID: 0,
    groupID: null,
    showAnomalies: false,
    sigsort: 'timeCreated',
    sortDirection: 1,
    showTabEdit: function (tabID) {
        jContext.hide();
        var tab = jMap.returnOpenTab(tabID);
        var outputHeader = 'Edit Tab <i>' + tab.tabName + '</i>';
        var outputBody = '<table>';
        outputBody += '<tr><td><input id ="tabrenametext" type="text" value="' + tab.tabName + '"></td><td><input type="submit" onClick="jUI.renameTab(' + tab.tabID + ')" value="rename"></td></tr>';
        outputBody += '<tr><td></td><td><input type="submit" onClick="jUI.deleteTab(' + tab.tabID + ')" value="delete"></td></tr>';
        outputBody += '</table>';
        $('#tabeditwindow .header .content').html(outputHeader);
        $('#tabeditwindow .body').html(outputBody);
        $('#tabeditwindow').show();
        $('#tabrenametext').keypress(function (e) {
            if (e.which == 13) {
                jUI.renameTab(tab.tabID);
                return false;
            }
        });
        $('#tabrenametext').focus();
        $('#tabrenametext').select();
    },
    close: function (windowID) {
        $('#' + windowID).hide();
    },
    editShowSigWindow: function (_editSig) {
        jWindow.edit = _editSig;
        jWindow.drawSigWindow();
        $('#signaturedetailtext').focus();
        $('#signaturedetailtext').select();
        $('#signaturewindow').show();
    },
    cancelEditSigWindow: function () {
        jWindow.edit = '';
        jWindow.drawSigWindow();
        $('#signaturewindow').show();
    },
    showSigWindow: function (_solarSystemID, _tabID) {        
        jWindow.edit = '';
        jWindow.solarSystemID = _solarSystemID;
        jWindow.tabID = _tabID;
        jWindow.drawSigWindow();
        $('#signaturewindow').show();
    },
    sortSigWindow: function(_sort) {
      jWindow.sigsort = _sort;
      jWindow.sortDirection = jWindow.sortDirection*-1;
      jWindow.drawSigWindow();
      $('#signaturewindow').show();
    },
    drawSigWindow: function () {        
        var system = jMap.returnMapSystem(jWindow.solarSystemID);
        if (system === false) {
            console.log("no such system " + jWindow.solarSystemID);
            return false;
        }
        var systemSignatures = jMap.returnSignatures(jWindow.solarSystemID, jWindow.tabID);
        if (typeof(systemSignatures)=='undefined') {
            return false;
        }
        if (jWindow.sigsort!=null) {
            systemSignatures.sort(function(a,b){
            if(a[jWindow.sigsort] < b[jWindow.sigsort]) return 1*jWindow.sortDirection;
            if(a[jWindow.sigsort] > b[jWindow.sigsort]) return -1*jWindow.sortDirection;
            return 0;
        });
        }

        var outputHeader = 'Signatures <i>' + system.solarSystemName + '</i>';
        var outputBody = '<table class="fixed">';
        outputBody += '<col><col width="80px"><col width="250px"><col><col><col><col>';
        outputBody += '<tr><td colspan = 6><b>show anomalies <input id="checkshowanoms" type=checkbox '+(jWindow.showAnomalies?'checked':'')+'></td></tr>';
        outputBody += '<tr><th onClick="jWindow.sortSigWindow(\'sig\')"><font style="cursor: pointer" >signature</font></th>';
        outputBody += '<th onClick="jWindow.sortSigWindow(\'type\')" ><font style="cursor: pointer" >type</font></th>';
        outputBody += '<th onClick="jWindow.sortSigWindow(\'detail\')"><font style="cursor: pointer" >detail</font></th>';
        outputBody += '<th onClick="jWindow.sortSigWindow(\'timeCreated\')"><font style="cursor: pointer" >open</font></th>';
        outputBody += '<th></th><th><img class="buttonlarge" onClick="jUI.deleteOldSigs(' + jWindow.solarSystemID + ',' + jWindow.tabID + ')" title="delete old" src="img/deleteall.png"></th></tr>';
        $.each(systemSignatures, function (index, signature) {
            if (jWindow.edit != signature.sig && (signature.isAnom == false || jWindow.showAnomalies==true)) {
                var trClass = '';
                if (signature.isAnom == true) {
                    trClass = 'anom';
                }
                if (signature.isOld == true) {
                    trClass = 'old';
                }
                outputBody += '<tr class="sigrow ' + trClass + '"><td class="sig">' + signature.sig + '</td>';
                outputBody += '<td>' + signature.type + '</td><td class="signaturedetail" data-sig="'+signature.sig+'">' + signature.detail + '</td><td>' + MakeTime(signature.timeCreated) + '</td>';
                outputBody += '<td><img class="buttonsmall" onClick = "jWindow.editShowSigWindow(\'' + signature.sig + '\')" src="img/edit.png"></td>';
                outputBody += '<td><img onClick="jUI.deleteSig(\'' + signature.sig + '\',' + jWindow.solarSystemID + ',' + jWindow.tabID + ')" class="buttonsmall" src="img/delete.png"></td>';                
                outputBody += '</tr>';
            } else if(jWindow.edit == signature.sig) {
                outputBody += '<tr class="sigrow ' + trClass + '"><td class="sig">' + signature.sig + '</td>';
                outputBody += '<td><input type=text id="signaturetypetext" style="width:75px" value="' + signature.type + '"></td><td><input type=hidden id="isanomhidden" value=' + signature.isAnom + '> <input id="signaturedetailtext" style="width:240px" type=text value="' + signature.detail + '"></td><td>' + MakeTime(signature.timeCreated) + '</td>';
                outputBody += '<td><img class="buttonsmall" onClick = "jUI.editSignature(\'' + signature.sig + '\',' + jWindow.solarSystemID + ','+jWindow.tabID+')" src="img/save.png"></td>';
                outputBody += '<td><img class="buttonsmall" onClick = "jWindow.cancelEditSigWindow()" src="img/cancel.png"></td>';
                outputBody += '</tr>';
            }

        });
        outputBody += '</table>';
        $('#signaturewindow .header .content').html(outputHeader);
        $('#signaturewindow .body').html(outputBody);
        $('#signaturedetailtext').keypress(function (e) {
            if (e.which == 13) {
                jUI.editSignature(jWindow.edit,jWindow.solarSystemID,jWindow.tabID);
                return false;
            }
        });
        $('#signaturetypetext').keypress(function (e) {
            if (e.which == 13) {
                jUI.editSignature(jWindow.edit,jWindow.solarSystemID,jWindow.tabID);
                return false;
            }
        });
        $('.signaturedetail').dblclick(function(e) {            
            jWindow.editShowSigWindow($(this).data('sig'));
        });
        $('#checkshowanoms').change(function() {
            jWindow.showAnomalies = $(this).is(":checked");
            jWindow.drawSigWindow();
        });
    },
    showPOIWindow: function () {
        this.drawPOIWindow();
        $('#poiwindow').show();
        $('#poisystemtext').autocomplete({source: function (request, response) {
                var results = $.ui.autocomplete.filter(jSystemList, request.term);
                response(results.slice(0, 10));
            }});
        $('#poisystemtext').focus();                    
    },
    drawPOIWindow: function () {
        var self = this;
        if (this.groupID === null) {
            jWindow.groupID = typeof (jGroupSelector.selectOptions[0]) != 'undefined' ? jGroupSelector.selectOptions[0].value : null;
        }
        var outputHeader = 'Points of interest';
        var outputBody = '';
        outputBody += '<select id="poigroupselector">';
        outputBody += jGroupSelector.getOutput();
        outputBody += '</select>';
        outputBody += '<table>';
        var poiList = new Array();
        outputBody += '<tr><th>System</th><th>description</th><th>capital</th><th></th></tr>';
        outputBody += '<tr><td><input type=text id="poisystemtext" ></td><td><input id="poidesctext" type=text></td><td><input id="poicapcheck" type="checkbox"></td><td><input onClick="jUI.addPOI()" type=submit value=add></td></tr>';
        $.each(jMap.POIs, function (index, poi) {
            if (($.grep(poiList, function (e) {
                return e.id == poi.id;
            })).length === 0) {
                poiList.push(poi);
                var cap = poi.type === 'cap' ? 'yes' : '';
                if (poi.type !== 'hub' && poi.groupID == jWindow.groupID) {
                    outputBody += '<tr><td>' + poi.poiSolarSystemName + '</td><td>' + poi.description + '</td><td>' + cap + '</td>';
                    outputBody += '<td><img onClick="jUI.deletePOI(' + poi.id + ','+jWindow.groupID+')" class="buttonsmall" src="img/delete.png"></td></tr>';
                }

            }
        });
        outputBody += '</table>';
        $('#poiwindow .header .content').html(outputHeader);
        $('#poiwindow .body').html(outputBody);

        if (this.groupID !== null) {
            $('#poigroupselector').val(this.groupID);
        } else {
            this.groupID = $('#poigroupselector').val();
        }
        $('#poigroupselector').change(function () {
            self.groupID = $('#poigroupselector').val();
            jWindow.showPOIWindow();
        });

        if ($('#poisystemtext').is(':visible')) {
            $('#poisystemtext').autocomplete({source: function (request, response) {
                    var results = $.ui.autocomplete.filter(jSystemList, request.term);
                    response(results.slice(0, 10));
                }});            
        }
        $('#poidesctext').keypress(function (e) {
                if (e.which == 13) {
                    jUI.addPOI();
                    return false;
                }
            });

    },
    showExitWindow: function () {
        this.drawExitWindow();
        $('#exitwindow').show();
        $('#exitsystemtext').autocomplete({source: function (request, response) {
                var results = $.ui.autocomplete.filter(jSystemList, request.term);
                response(results.slice(0, 10));
            }});
        $('#exitsystemtext').focus();
    },
    drawExitWindow: function (_json) {
        var solarSystemName = $('#exitsystemtext').val();
        $('#exitsystemtext').val('');
        var outputHeader = 'Exitfinder';
        if (typeof (solarSystemName) != 'undefined') {
            outputHeader += ': <i>' + solarSystemName + '</i>';
        }
        var outputBody = '<input type=text id="exitsystemtext"> <input type=submit onClick="jUI.findExit()" value="find" >';
        outputBody += '<table>';
        if (typeof (_json) != 'undefined') {
            outputBody += '<tr><th>system</th><th>jumps</th><th>distance</th></tr>';
            $.each(_json, function (index, exit) {
                outputBody += '<tr><td>' + exit.solarSystemName + '</td><td>' + jUtils.returnJumpString(exit.js, exit.j) + '</td><td>' + +parseFloat(exit.ly).toFixed(2) + 'ly</td></tr>';
            });
        }
        outputBody += '</table>';
        $('#exitwindow .header .content').html(outputHeader);
        $('#exitwindow .body').html(outputBody);
        $('#exitsystemtext').keypress(function (e) {
            if (e.which == 13) {
                jUI.findExit()
                return false;
            }
        });
        $('#exitsystemtext').focus();

    },
    drawAdminWindow: function () {

    },
    showAdminWindow: function () {
        this.drawAdminWindow();
        $('#adminwindow').show();
    },
    drawAccountWindow: function(_json) {
        var outputBody = '';
        outputBody +='<table>';
        outputBody +='<tr><th>character</th><th>corporation</th><th>alliance</th><th></th></tr>';
        
        $.each(_json, function(index,element) {
            var corpimg = element.corporationID!=0?'<img src="https://image.eveonline.com/Corporation/'+element.corporationID+'_32.png" style="vertical-align: middle;">':'';
            var allyimg = element.allianceID!=0?'<img src="https://image.eveonline.com/Alliance/'+element.allianceID+'_32.png" style="vertical-align: middle;">':'';
            outputBody +='<tr><td>'+element.characterName+'</td><td>'+corpimg+'<span style="vertical-align: middle;">'+element.corporationName+'</span>'+'</td>';
            outputBody +='<td>'+allyimg+'<span style="vertical-align: middle;">'+element.allianceName+'</span>'+'</td>';
            outputBody +='<td><img onClick="jUI.deleteCharacter(\'' + element.characterID + '\')" class="buttonsmall" src="img/delete.png"></td></tr>';
        });        
        
        outputBody +='</table>';
        outputBody +='<p><center><input type=submit value="add characters via the EVE-SSO" onClick="jUI.redirectSSO()" class="link">';
        
        $('#accountwindow .body').html(outputBody);
        $('#accountwindow').show();
    },
    showAccountWindow: function() { 
        jUI.loadCharacters();        
    }
};

var jContext = {
    hide: function () {
        $('#context').hide();
    },
    showSystemContext: function (_mapSystem, event) {
        var contextOutput = '<table>';

        //check if system is selected and there are multiple more selected (-> delete chain option)
        if ($('#' + _mapSystem.divID).hasClass('selected') && $('.selected').length > 1) {
            contextOutput += '<tr><td class="icon"><img id="cloneall" src="img/clone.png" title="clone"></td>';
            contextOutput += '<td class="icon"><img id="pushall" src="img/push.png" title="push"></td>';
            contextOutput += '<td class="icon"><img id="deleteall" src="img/deleteall.png" title="delete selected"></td>';
        } else {
            contextOutput += '<tr>';
            contextOutput += '<td class="icon"><img  onClick="jWindow.showSigWindow(' + _mapSystem.solarSystemID + ',' + vGlobals.activeTabID + ')" src="img/radar.png" title="signatures"></td>';
            contextOutput += '<td class="icon"><a href="https://zkillboard.com/system/' + _mapSystem.solarSystemID + '" target=blank><img id="zkill" src="img/zkill.png" title="zKillboard"></a></td>';
            contextOutput += '<td class="icon"><img id="setpin" src="img/' + (_mapSystem.locked ? 'depin.png' : 'pin.png') + '" title="pin"></td>';
            contextOutput += '<td class="icon"><img id="setrallypoint" src="img/' + (_mapSystem.rallypoint ? 'denotice.png' : 'notice.png') + '" title="rallypoint"></td>';
            contextOutput += '<td class="icon"><img id="sethome" src="img/' + (_mapSystem.home ? 'dehome.png' : 'home.png') + '" title="home"></td>';
            contextOutput += '<td class="icon"></td>';
            contextOutput += '<td class="icon"><img id="deleteone" src="img/delete.png" title="delete"></td>';
            contextOutput += '</tr></table>';
            contextOutput += '<table><tr><td><input id="inteltext" type=text placeholder="intel"></td>';
            contextOutput += '<td><input type=submit value="add" onClick="jUI.addIntel(\'intel\',\'' + _mapSystem.solarSystemID + '\',\'' + _mapSystem.tabID + '\')"></td></tr>';
            contextOutput += ' <tr><td><input id="postext" type=text placeholder="P7M42"></td>';
            contextOutput += '<td><input type=submit value="add" onClick="jUI.addIntel(\'pos\',\'' + _mapSystem.solarSystemID + '\',\'' + _mapSystem.tabID + '\')"></td></tr>';
        }

        //------------------       
        contextOutput += '</table>';
        $('#context').html(contextOutput);

        //-------bind buttons-----------
        $('#sethome').click(function () {
            jUI.toggleHomeMapSystem(_mapSystem);
            jContext.hide();
        });
        $('#setrallypoint').click(function () {
            jUI.toggleRallypoint(_mapSystem);
            jContext.hide();
        });
        $('#setpin').click(function () {
            jUI.togglePinMapSystem(_mapSystem);
            jContext.hide();
        });
        $('#deleteone').click(function () {
            //deleteMapSystems expexts an array of systems-> create an array with one system in it
            var deleteSystems = new Array();
            deleteSystems.push(_mapSystem);
            jUI.deleteMapSystems(deleteSystems);
            jContext.hide();
        });
        $('#deleteall').click(function () {
            //get the tab of the clicked system
            var tab = jMap.returnOpenTab(_mapSystem.tabID);
            //go through all systems on that tab and check for systems that are selected
            var deleteSystems = new Array();
            $.each(tab.MapSystems, function (index, system) {
                if ($('#' + system.divID).hasClass('selected')) {
                    deleteSystems.push(system);
                }
            });
            jUI.deleteMapSystems(deleteSystems);
            jContext.hide();
        });
        $('#cloneall').click(function () {
            //get the tab of the clicked system
            var tab = jMap.returnOpenTab(_mapSystem.tabID);
            //go through all systems on that tab and check for systems that are selected
            var cloneSystems = new Array();
            $.each(tab.MapSystems, function (index, system) {
                if ($('#' + system.divID).hasClass('selected')) {
                    cloneSystems.push(system);
                }
            });
            jContext.showCloneTabSelector(event, cloneSystems, false);
            //jUI.cloneMapSystems(cloneSystems,false);
            //jContext.hide();
        });
        $('#pushall').click(function () {
            //get the tab of the clicked system
            var tab = jMap.returnOpenTab(_mapSystem.tabID);
            //go through all systems on that tab and check for systems that are selected
            var cloneSystems = new Array();
            $.each(tab.MapSystems, function (index, system) {
                if ($('#' + system.divID).hasClass('selected')) {
                    cloneSystems.push(system);
                }
            });
            jContext.showCloneTabSelector(event, cloneSystems, true);
            //jUI.cloneMapSystems(cloneSystems,true);
            //jContext.hide();
        });
        
        //---------bind enter key-----------
        $('#inteltext').keypress(function (e) {
            if (e.which == 13) {
                jUI.addIntel('intel',_mapSystem.solarSystemID,_mapSystem.tabID);
                return false;
            }
        });
        $('#postext').keypress(function (e) {
            if (e.which == 13) {
                jUI.addIntel('pos',_mapSystem.solarSystemID,_mapSystem.tabID);
                return false;
            }
        });

        //------------------
        jContext.show(event);
    },
    showTabContext: function (tab, event) {
        var outputContext = '';
        outputContext += '<div id="contextedit"  onClick="jWindow.showTabEdit(' + tab.tabID + ')" class="entry">edit</div>';
        outputContext += '<div id="contextclose" class="entry" onClick="jUI.closeTab(' + tab.tabID + ')">close</div>';
        $('#context').html(outputContext);
        jContext.show(event);
    },
    showConnectionContext: function (connection, event) {
        var contextOutput = '<table>';
        contextOutput += '<tr><td class="icon"><img id="neol" class="constatus" src="img/neol.png"></td><td class="icon"><img id="eol" class="constatus" src="img/eol.png" title="end of life"></td>';
        contextOutput += '<td class="icon"></td><td class="icon"><img id="deletecon" class="constatus" src="img/delete.png" title="delete"></td></tr>';
        contextOutput += '<tr><td class="icon"><img id="massgreen" class="constatus" src="img/massgreen.png" title="first mass"></td>';
        contextOutput += '<td class="icon"><img id="massyellow" class="constatus" src="img/massyellow.png" title="second mass"></td>';
        contextOutput += '<td class="icon"><img id="massred" class="constatus" src="img/massred.png" title="crit mass"></td>';
        contextOutput += '<td class="icon"><img id="massblue" class="constatus" src="img/massblue.png" title="frig connection"></td></tr>';
        contextOutput += '</table>';
        $('#context').html(contextOutput);

        $('.constatus').click(function (event) {
            var id = event.target.id;
            switch (id) {
                case 'neol':
                    jUI.setConnectionTimeStage(connection, 1);
                    break;
                case 'eol':
                    jUI.setConnectionTimeStage(connection, 2);
                    break;
                case 'massgreen':
                    jUI.setConnectionMassStage(connection, 1);
                    break;
                case 'massyellow':
                    jUI.setConnectionMassStage(connection, 2);
                    break;
                case 'massred':
                    jUI.setConnectionMassStage(connection, 3);
                    break;
                case 'massblue':
                    jUI.setConnectionMassStage(connection, 4);
                    break;
                case 'deletecon':
                    jUI.deleteConnection(connection);
                    break;
            }
            jContext.hide();
        })

        //------------------
        jContext.show(event);
    },
    showLabelContext: function (_mapSystem, event) {
        var outputContext = '';
        outputContext += '<input id="editlabeltext" class="label" type=text value="' + _mapSystem.label + '">';
        outputContext += '<input type=submit onClick="jUI.setLabel(\'' + _mapSystem.solarSystemID + '\',\'' + _mapSystem.tabID + '\')" value="set">';
        $('#context').html(outputContext);
        event.pageY -= 25;
        jContext.show(event);

        $('#editlabeltext').selectRange(3, $('#editlabeltext').val().length);
        $('#editlabeltext').keypress(function (event) {
            if (event.which === 13) {
                event.preventDefault();
                jUI.setLabel(_mapSystem.solarSystemID, _mapSystem.tabID);
            }
        });


    },
    showMapContext: function (event) {
        var outputContext = '';
        outputContext += ' <b>from</b> <input type=text id="fromsystemtext" > <b>to</b> <input type= text id="tosystemtext">';
        outputContext += '<input type=submit onClick="jUI.addConnection()" value="add">';
        $('#context').html(outputContext);
        event.pageY -= 20;
        jContext.show(event);
        $('#fromsystemtext').autocomplete({source: function (request, response) {
                var results = $.ui.autocomplete.filter(jSystemList, request.term);
                response(results.slice(0, 10));
            }});
        $('#tosystemtext').autocomplete({source: function (request, response) {
                var results = $.ui.autocomplete.filter(jSystemList, request.term);
                response(results.slice(0, 10));
            }});                
        $('#fromsystemtext').keypress(function (e) {
            if (e.which == 13) {
                jUI.addConnection();
                return false;
            }
        });
        $('#tosystemtext').keypress(function (e) {
            if (e.which == 13) {
                jUI.addConnection();
                return false;
            }
        });
        $('#fromsystemtext').focus();
        
    },
    showCloneTabSelector: function (event, _cloneSystems, _deleteoriginal) {
        var output = '<table><tr><td><b>';
        output += '' + (_deleteoriginal == true ? 'push' : 'clone') + ' to tab </b></td><td><select name="clonetab" id="clonetabselector">';
        $.each(jTabSelector.selectOptions, function (index, option) {
            if (vGlobals.activeTabID != option.value) {
                output += '<option value="' + option.value + '">' + option.text + '</option>';
            }
        });
        output += '</select></td><td><input id="clonesubmit" type=submit value="go"></td></tr></table>'
        $('#context').html(output);
        $('#clonesubmit').click(function (event) {
            jUI.cloneMapSystems(_cloneSystems, _deleteoriginal);
            event.preventDefault();
            jContext.hide();
        });

    },
    showNewTabGroupSelector: function(event) {
        var outputContext = 'create new tab for ';
        outputContext += '<select id=tabgroupselect>';
        outputContext +=jGroupSelector.getOutput();
        outputContext +='</select> ';
        outputContext +='<input type=submit id=submitnewtab value="go"> ';
        $('#context').html(outputContext);        
        jContext.show(event);
        
        $('#submitnewtab').click(function(){
            var groupID = $('#tabgroupselect').val();
            jUI.createTab(groupID);
            jContext.hide();
        });
    },
    show: function (event) {
        $('#context').css({position: 'absolute', top: event.pageY, left: event.pageX});
        $('#context').show();
    }
};
var jTabSelector = {
    selectOptions: new Array(),
    addTab: function (_json) {
        jTabSelector.selectOptions.push(new jSelectorOption(_json.ti, _json.tn));
        jTabSelector.redraw();
    },
    addTabs: function (_json) {
        $.each(_json, function(index, element){
            jTabSelector.selectOptions.push(new jSelectorOption(element.tabID, element.tabName));
        });
        jTabSelector.redraw();
    },
    removeTab: function (_tabID) {
        // grep returns entrys that don't satisfy value=tabID
        jTabSelector.selectOptions = jQuery.grep(jTabSelector.selectOptions, function (e) {
            return  e.value == _tabID;
        }, true);
        jTabSelector.redraw();
    },
    renameTab: function (_tabID, _tabName) {
        var optionArr = jQuery.grep(jTabSelector.selectOptions, function (e) {
            return  e.value == _tabID;
        });
        if (optionArr.length > 0) {
            var option = optionArr[0];
            option.text = _tabName;
            console.log(option);
            jTabSelector.redraw();
        }
    },
    redraw: function () {
        var output = '';
        output += '<option value="delimiter" ></option>';        
        $.each(jTabSelector.selectOptions, function (index, option) {
            if (jMap.returnOpenTab(option.value) === false) {
                output += '<option value="' + option.value + '">' + option.text + '</option>';
            }
        });
        $('#newtabselector').html(output);
    }
};
var jGroupSelector = {
    selectOptions: new Array(),
    addGroup: function (_json) {
        jGroupSelector.selectOptions.push(new jSelectorOption(_json.groupID,_json.title));
    },
    addGroups: function (_json) {        
        $.each(_json, function(index,element) {
            try{
                jGroupSelector.selectOptions.push(new jSelectorOption(element.groupID,element.title));
            }catch(e) {
                console.log(e);
            }            
        });
    },
    getOutput: function () {
        var output = '';
        $.each(jGroupSelector.selectOptions, function (index, option) {
            output += '<option value="' + option.value + '">' + option.text + '</option>';
        });
        return output;
    }
};
var jTabOptions = {
    show: function (tab) {
        var outputContent = '';
        var outputHeader = '';
    }
};
var jWormholeList = {
    list: new Array(),
    init: function (_json) {
        $.each(_json, function (index, hole) {
            jWormholeList.list.push(new jWormhole(hole));
        });
    },
    /**     
     * @param string _identifier
     * @returns jWormhole
     */
    getWormhole: function (_identifier) {
        var arr = $.grep(jWormholeList.list, function (e) {
            return e.identifier == _identifier
        });
        if (arr.length > 0) {
            return arr[0];
        } else {
            return false;
        }
    }
};
var jEffectList = {
    list: new Array(),
    init: function (_json) {
         $.each(_json, function (index, effect) {
            jEffectList.list.push(new jEffect(effect));
        });
    },
    getEffectArray: function (_class, _effect) {
        if ((_class > 30) && (_class < 40))
            _class -= 30; //shattered systems
        if ((_class > 40) && (_class < 50))
            _class = 6; //frig systems have class 6 effects
        return $.grep(jEffectList.list, function (e) {
            return (e.class == _class) && (e.effect == _effect);
        });
    }
};

var jUI = {
    closeTab: function (_tabID) {
        jContext.hide();
        //send message to client        
        var messageArray = new Array(new jMessage('closeTab', {tabID: _tabID}));
        jServer.sendRequest('request.php', messageArray);
    },
    deleteTab: function (_tabID) {
        jWindow.close('tabeditwindow');
        var messageArray = new Array(new jMessage('DeleteTab', {tabID: _tabID}));
        jServer.sendRequest('request.php', messageArray);
    },
    renameTab: function (_tabID) {
        var newTabName = $('#tabrenametext').val();
        console.log(newTabName);
        jWindow.close('tabeditwindow');
        var messageArray = new Array(new jMessage('RenameTab', {tabID: _tabID, tabName: newTabName}));
        jServer.sendRequest('request.php', messageArray);
    },
    createTab: function (_groupID) {
        //send message to client
        var messageArray = new Array(new jMessage('CreateTab', {groupID: _groupID}));
        jServer.sendRequest('request.php', messageArray);
    },
    updateDraggedSystems: function (_mapSystems) {
        var messageArray = new Array();
        $.each(_mapSystems, function (index, system) {
            //get the current position
            var newMapX = $('#' + system.divID).position().left - vConstants.mapCenter;
            var newMapY = $('#' + system.divID).position().top - vConstants.mapCenter;
            system.dragged = false;
            if ((newMapX != system.mapX) || (newMapY != system.mapY)) { //only update system when position really changed
                messageArray.push(new jMessage('moveSystem', {
                    tabID: system.tabID,
                    mapX: newMapX,
                    mapY: newMapY,
                    solarSystemID: system.solarSystemID
                }));
                system.mapX = newMapX;
                system.mapY = newMapY;
            }
        });
        if (messageArray.length > 0) { //only send drag messages when there are any
            jServer.sendRequest('request.php', messageArray);
        }

    },
    deleteMapSystems: function (_mapSystemArr) {
        var messageArray = new Array();
        $.each(_mapSystemArr, function (index, system) {
            messageArray.push(new jMessage('deleteSystem', {tabID: system.tabID, solarSystemID: system.solarSystemID}));
        });
        jServer.sendRequest('request.php', messageArray);
    },
    cloneMapSystems: function (_mapSystemArr, _deleteOriginal) {
        var messageArray = new Array();
        var solarSystemIDArr = new Array();
        var fromTabID = 0;
        var toTabID = $('#clonetabselector').val();
        $.each(_mapSystemArr, function (index, system) {
            solarSystemIDArr.push(system.solarSystemID);
            fromTabID = system.tabID;
        });
        messageArray.push(new jMessage('copychain', {fromTabID: fromTabID, toTabID: toTabID, solarSystemIDs: solarSystemIDArr, deleteSource: _deleteOriginal}));
        jServer.sendRequest('request.php', messageArray);

        try {
            jMap.returnOpenTab(toTabID).makeActive();
        } catch (e) {

        }
    },
    togglePinMapSystem: function (_mapSystem) {
        var messageArray = new Array();
        messageArray.push(new jMessage('setsystemstatus', {tabID: _mapSystem.tabID, solarSystemID: _mapSystem.solarSystemID, systemStatus: 'pin', state: !_mapSystem.locked}));
        jServer.sendRequest('request.php', messageArray);
    },
    toggleHomeMapSystem: function (_mapSystem) {
        var messageArray = new Array();
        messageArray.push(new jMessage('setsystemstatus', {tabID: _mapSystem.tabID, solarSystemID: _mapSystem.solarSystemID, systemStatus: 'home', state: !_mapSystem.home}));
        jServer.sendRequest('request.php', messageArray);
    },
    toggleRallypoint: function (_mapSystem) {
        var messageArray = new Array();
        messageArray.push(new jMessage('setsystemstatus', {tabID: _mapSystem.tabID, solarSystemID: _mapSystem.solarSystemID, systemStatus: 'rallypoint', state: !_mapSystem.rallypoint}));
        jServer.sendRequest('request.php', messageArray);
    },
    setConnectionTimeStage: function (_connection, _stage) {
        var messageArray = new Array();
        messageArray.push(new jMessage('ConnectionStage', {tabID: _connection.tabID, fromSolarSystemID: _connection.sourceMapSystem.solarSystemID, toSolarSystemID: _connection.targetMapSystem.solarSystemID, timeStage: _stage}));
        jServer.sendRequest('request.php', messageArray);
    },
    setConnectionMassStage: function (_connection, _stage) {
        var messageArray = new Array();
        messageArray.push(new jMessage('ConnectionStage', {tabID: _connection.tabID, fromSolarSystemID: _connection.sourceMapSystem.solarSystemID, toSolarSystemID: _connection.targetMapSystem.solarSystemID, massStage: _stage}));
        jServer.sendRequest('request.php', messageArray);
    },
    deleteConnection: function (_connection) {
        var messageArray = new Array();
        messageArray.push(new jMessage('DeleteConnection', {tabID: _connection.tabID, fromSolarSystemID: _connection.sourceMapSystem.solarSystemID, toSolarSystemID: _connection.targetMapSystem.solarSystemID}));
        jServer.sendRequest('request.php', messageArray);
    },
    setLabel: function (_solarSystemID, _tabID) {
        var label = $('#editlabeltext').val();
        jContext.hide();
        var messageArray = new Array();
        messageArray.push(new jMessage('setlabel', {tabID: _tabID, solarSystemID: _solarSystemID, label: label}));
        jServer.sendRequest('request.php', messageArray);
    },
    pasteSigs: function (_sigPasteParse, _solarSystemID, _tabID) {
        var messageArray = new Array();
        var pasteArray = new Array();
        var showAnom = $('#checkshowanoms').is(':checked');
        $.each(_sigPasteParse, function (index, line) {
            pasteArray.push({                
                sig: line.sig,
                isAnom: line.isanom,
                type: line.type,
                detail: line.detail,                
            });
        });
        console.log(_sigPasteParse);
        if (pasteArray.length > 0) { //only send messages when there are any
            messageArray.push(new jMessage('SigPaste',{solarSystemID:_solarSystemID, tabID:_tabID,paste:pasteArray,showAnoms:showAnom}));
            jServer.sendRequest('request.php', messageArray);
        }
    },
    deleteSig: function (_sig, _solarSystemID, _tabID) {        
        var _sigArr = new Array();
        _sigArr.push(_sig);
        jUI.deleteSigs(_solarSystemID,_tabID,_sigArr);        
    },
    deleteSigs: function(_solarSystemID,_tabID,_sigArr) {        
        var messageArray = new Array();        
        messageArray.push(new jMessage('DeleteSignatures', {
            solarSystemID: _solarSystemID,           
            tabID: _tabID,
            sigs: _sigArr
        }));
        jServer.sendRequest('request.php', messageArray);
    },
    deleteOldSigs: function (_solarSystemID, _tabID) { 
        var tab = jMap.returnOpenTab(_tabID);
        if (tab==null) {
            console.log('tab doesn\'t exist');
            return false;
        }
        var _groupID = tab.groupID;        
        var sigArr = new Array;        
        $.each(jMap.Signatures, function(index,element) {
            if (element.solarSystemID == _solarSystemID && element.groupID == _groupID && element.isOld==true) {
                //only delete anoms if they are shown in the sigwindow                
                if (jWindow.showAnomalies==true || element.isAnom==false) {
                    sigArr.push(element.sig);
                }                
            }
        }); 
        jUI.deleteSigs(_solarSystemID,_tabID,sigArr);
    },
    editSignature: function (_sig, _solarSystemID,_tabID) {
        var sigType = $('#signaturetypetext').val();
        var sigDetail = $('#signaturedetailtext').val();
        var isAnom = $('#isanomhidden').val();
        var messageArray = new Array();
        var pasteArray = new Array({sig: _sig, type: sigType, detail: sigDetail, isAnom: isAnom});
        messageArray.push(new jMessage('SigPaste',{solarSystemID:_solarSystemID, tabID:_tabID,paste:pasteArray}));
        jServer.sendRequest('request.php', messageArray);
    },
    addIntel: function (_type, _solarSystemID, _tabID) {
        var value = '';
        var messageArray = new Array();
        if (_type === 'intel') {
            value = $('#inteltext').val();
            $('#inteltext').val('');
        } else {
            value = $('#postext').val();
            value = value.replace('p', 'P');
            value = value.replace('m', 'M');
            $('#postext').val('');
        }
        messageArray.push(new jMessage('AddIntel', {solarSystemID: _solarSystemID, type: _type, value: value, tabID: _tabID}));
        jServer.sendRequest('request.php', messageArray);
    },
    deleteIntel: function (_id,_groupID) {
        var messageArray = new Array();        
        messageArray.push(new jMessage('deleteintel', {id: _id,groupID:_groupID}));
        jServer.sendRequest('request.php', messageArray);
    },
    deletePOI: function (_id,_groupID) {
        var messageArray = new Array();
        messageArray.push(new jMessage('DeletePoi', {id: _id,groupID:_groupID}));
        jServer.sendRequest('request.php', messageArray);
    },
    addPOI: function () {
        var groupID = jWindow.groupID;
        if (groupID === null)
            return false;

        var solarSystemName = $('#poisystemtext').val();
        $('#poisystemtext').val('');

        var description = $('#poidesctext').val();
        $('#poidesctext').val('');

        var type = ($('#poicapcheck').is(':checked')) ? 'cap' : 'sub';
        var messageArray = new Array();
        messageArray.push(new jMessage('CreatePoi', {solarSystemName: solarSystemName, description: description, type: type, groupID: groupID}));
        jServer.sendRequest('request.php', messageArray);
    },
    findExit: function () {
        var solarSystemName = $('#exitsystemtext').val();
        if (solarSystemName === '') {
            return false;
        }
        var messageArray = new Array();
        messageArray.push(new jMessage('FindExit', {solarSystemName: solarSystemName, tabID: vGlobals.activeTabID}));
        jServer.sendRequest('request.php', messageArray);
    },
    addConnection: function () {
        var fromSystemName = $('#fromsystemtext').val();
        var toSystemName = $('#tosystemtext').val();
        $('#fromsystemtext').val('');
        $('#tosystemtext').val('');
        var messageArray = new Array();
        messageArray.push(new jMessage('addConnection', {fromSolarSystemName: fromSystemName, toSolarSystemName: toSystemName, tabID: vGlobals.activeTabID}));
        jServer.sendRequest('request.php', messageArray);
        jContext.hide();
    },
    loadCharacters: function() {
        var messageArray = new Array();
        messageArray.push(new jMessage('LoadCharacters', {}));
        jServer.sendRequest('request.php', messageArray);
    },
    redirectSSO: function() {
        window.location = 'addCharacter.php';
    },
    deleteCharacter: function(_characterID) {
        var messageArray = new Array();
        messageArray.push(new jMessage('DeleteCharacter', {characterID:_characterID}));
        jServer.sendRequest('request.php', messageArray);
    }
};
/**
 * mapObject
 * @type type
 */
var jMap = {
    /**
     * @type Array.<jMapTab> MapTabs
     */
    MapTabs: new Array(),
    Pilots: new Array(),
    Signatures: new Array(),
    Intels: new Array(),
    POIs: new Array(),
    /**
     * @return {jMapTab} tab
     * @param {number} _tabID
     */
    returnOpenTab: function (_tabID) {
        var toReturn = false;
        jQuery.each(jMap.MapTabs, function (index, tab) {
            if (tab.tabID == _tabID) {
                toReturn = tab;
                return tab;
            }
        });
        return toReturn;
    },
    addTab: function (_json) {
        //create new jmaptab object
        var newTab = new jMapTab(_json);

        //check if we already have a tab with that ID open
        if (jMap.returnOpenTab(newTab.tabID) === false) {
            //nope, we don't, -> add it to the open tabs
            jMap.MapTabs.push(newTab);

            //draw it            
            newTab.print();
            $('.map').bind('contextmenu', function (event) {
                jContext.showMapContext(event);
                event.preventDefault();
                event.stopPropagation();
            });
            newTab.makeActive();
            return true;
        } else
            return false;
    },
    closeTab: function (_tabID) {
        //get Tab
        var tab = jMap.returnOpenTab(_tabID);

        //check if we got it
        if (tab !== false) {
            //remove DOMs
            $('#map' + tab.divID).remove();
            $('#tabreg' + tab.divID).remove();

            //remove it from MapTabs            
            jMap.MapTabs = jQuery.grep(jMap.MapTabs, function (e) {
                return e.tabID == _tabID
            }, true);

            //redraw tabselector
            jTabSelector.redraw();
            if (typeof (jMap.MapTabs[0]) != 'undefined') {
                jMap.MapTabs[0].makeActive();
            }
        }
    },
    renameTab: function (_json, _tabID) {
        jTabSelector.renameTab(_tabID, _json.tabName);
        var tab = jMap.returnOpenTab(_tabID);
        if (tab !== false) {
            tab.rename(_json);
        } else {
            console.log("Tab " + _tabID + " doesn't exist!");
        }
    },
    addConnection: function (_json) {
        _tabID = _json.tabID;
        var tab = jMap.returnOpenTab(_tabID);
        if (tab !== false) {
            tab.addConnection(_json, _tabID);
        } else {
            console.log("couldn't add Connection: Tab " + _tabID + " doesn't exist!");
        }
    },
    dragSystem: function (_json) {
        var _tabID = _json.tabID;
        var tab = jMap.returnOpenTab(_tabID);
        if (tab !== false) {
            tab.moveSystem(_json);
        } else {
            console.log("Tab " + _tabID + " doesn't exist!");
        }
    },
    addSystem: function (_json) {
        var _tabID = _json.tabID;        
        var tab = jMap.returnOpenTab(_tabID);
        if (tab !== false) {
            tab.addSystem(_json);
            jMap.updatePilotCount();
        } else {
            console.log("Can't delete System: Tab " + _tabID + " doesn't exist!");
        }
    },
    deleteSystem: function (_json) {
        var _tabID = _json.tabID;
        var tab = jMap.returnOpenTab(_tabID);
        if (tab !== false) {
            tab.deleteSystem(_json);
        } else {
            console.log("couldn't add System: Tab " + _tabID + " doesn't exist!");
        }
    },
    switchTab: function (_tabID) {
        jMap.returnOpenTab(_tabID).makeActive();
    },
    turnOffGrid: function () {
        $('.map').css('background', 'none');
    },
    turnOnGrid: function () {
        $('.map').css('background', 'url(img/lattice.png)');
        $('.map').css('background-size', '62px 46px');
    },
    drawEmptyTab: function () {
        //remove old empty tab
        $('.tabempty').remove();

        //output for new empty tab
        var tabOutput = '<div id="tabempty" class="tab tabempty">';
        tabOutput += '<select name="newtab" id="newtabselector">';
        tabOutput += '</select>';
        tabOutput += ' <div id = "newtabadd" class="add">+</div></div>'

        //add new empty tab to the end of the tab display
        $('#mapheader').append(tabOutput);

        //fill tab selector with options
        jTabSelector.redraw();

        $('#newtabselector').change(function () {
            if ($('#newtabselector').val() != '') {
                //get tabID from the selector
                var _tabID = $('#newtabselector').val();

                //pull tab from server and open it
                jServer.pullTab(_tabID);
            }
        });

        //bind click event to the + button
        $('#newtabadd').click(function (event) {
            jContext.showNewTabGroupSelector(event);            
        });
    },
    setSystemStatus: function (_json) {
        var _tabID = _json.tabID;
        var tab = jMap.returnOpenTab(_tabID);
        if (tab !== false) {
            tab.setSystemStatus(_json);
        } else {
            console.log("couldn't add System: Tab " + _tabID + " doesn't exist!");
        }
    },
    setConnectionTimeStage: function (_json) {
        var sourceID = _json.fromSolarSystemID;
        var targetID = _json.toSolarSystemID;
        var eolTime = _json.eolTime;
        var timeStage = _json.timeStage;
        var groupID = _json.groupID;
        var connections = jMap.returnConnections(sourceID, targetID);

        $.each(connections, function (index, connection) {
            if (connection !== false) {
                if (connection.groupID == groupID) {
                    connection.timeStage = timeStage;
                    connection.eolTime = eolTime;
                    connection.updateColor();
                }                
            }
        });
    },
    setConnectionMassStage: function (_json) {
        var sourceID = _json.fromSolarSystemID;
        var targetID = _json.toSolarSystemID;
        var massStage = _json.massStage;
        var connections = jMap.returnConnections(sourceID, targetID);
        var groupID = _json.groupID;
        
        $.each(connections, function (index, connection) {

            if (connection !== false) {
                if (connection.groupID == groupID) {
                    connection.massStage = massStage;
                    connection.updateColor();
                }                
            }
        });
    },
    addConnectionMass: function (_json) {
        var sourceID = _json.fromSolarSystemID;
        var targetID = _json.toSolarSystemID;
        var groupID = _json.groupID;
        var mass = _json.addMass;
        var connections = jMap.returnConnections(sourceID, targetID);

        $.each(connections, function (index, connection) {

            if (connection !== false) {
                if (connection.groupID == groupID) {
                    connection.massPassed += mass;
                }
            }
        });
    },
    setSystemLabel: function (_json) {
        var _tabID= _json.tabID;
        var tab = jMap.returnOpenTab(_tabID);
        if (tab !== false) {
            tab.setSystemLabel(_json);
        } else {
            console.log("Tab " + _tabID + " doesn't exist!");
        }
    },
    deleteConnection: function (_json) {
        var _tabID = _json.tabID;
        var tab = jMap.returnOpenTab(_tabID);
        if (tab !== false) {
            tab.deleteConnection(_json);
        } else {
            console.log("Tab " + _tabID + " doesn't exist!");
        }
    },
    addPilot: function (_json) {
        var newPilot = new jPilot(_json);
        //check if we already have the pilot in our list
        var oldPilot = $.grep(jMap.Pilots, function (e) {
            return e.characterName === newPilot.characterName;
        });

        if (oldPilot.length > 0) {
            //we already have that pilot ->update him            
            oldPilot = oldPilot[0];
            oldPilot.solarSystemID = newPilot.solarSystemID;
            oldPilot.shipTypeName = newPilot.shipTypeName;
        } else {
            //add pilot to the list
            jMap.Pilots.push(newPilot);
        }
        jMap.updatePilotCount();
    },
    updatePilotCount: function () {
        $('.pilotnumber').each(function () {
            var solarSystemID = $(this).attr('value');
            var systemPilots = $.grep(jMap.Pilots, function (e) {
                return e.solarSystemID == solarSystemID;
            });
            var pilotNumber = systemPilots.length;
            var output = '<b>Pilots: </b>' + pilotNumber;
            $.each(systemPilots, function (index, pilot) {
                if (pilot.characterName == vGlobals.characterName) {
                    output += '<b>&#x265A;</b>';
                }
            });
            $(this).html(output);
        });
    },
    returnConnections: function (_sourceID, _targetID) {
        var connections = jsPlumb.getConnections("*");
        var connectionArr = $.grep(connections, function (e) {
            return ((e.sourceMapSystem.solarSystemID == _sourceID && e.targetMapSystem.solarSystemID == _targetID) ||
                    (e.sourceMapSystem.solarSystemID == _targetID && e.targetMapSystem.solarSystemID == _sourceID))
        });
        return connectionArr;
    },
    setPilotSolarSystemID: function (_solarSystemID) {
        vGlobals.pilotSolarSystemID = _solarSystemID;
        jWindow.solarSystemID = _solarSystemID;
        if ($('#signaturewindow').is(':visible')) {
            jWindow.drawSigWindow();
        }
        
    },
    offlineCharacter: function (_characterName) {
        console.log(_characterName+' is now offline');
        jMap.Pilots = $.grep(jMap.Pilots, function (e) {
            return e.characterName === _characterName;
        }, true);
        jMap.updatePilotCount();
    },
    addSignature: function (_json) {
        var signature = new jSignature(_json);
        var oldSignature = $.grep(jMap.Signatures, function(e){
            return e.groupID == signature.groupID && e.sig == signature.sig && e.solarSystemID==signature.solarSystemID;
        });
        if (oldSignature.length === 0) {
            //new signature
            jMap.Signatures.push(signature);
        }else{
            //update signature            
            oldSignature[0].isAnom = signature.isAnom;
            oldSignature[0].type = (signature.type!=='')? signature.type:oldSignature[0].type;
            oldSignature[0].detail = (signature.detail!=='')?signature.detail:oldSignature[0].detail;
            oldSignature[0].isOld = signature.isOld;
        }
        
        if (jWindow.solarSystemID == signature.solarSystemID) {            
            jWindow.drawSigWindow();
        }
    },    
    deleteSignature: function (_json) {
        var solarSystemID = _json.solarSystemID;
        var sig = _json.sig;
        var groupID = _json.groupID;
        jMap.Signatures = $.grep(jMap.Signatures, function (e) {
            return (e.solarSystemID == solarSystemID) && (e.sig == sig) && (e.groupID == groupID);
        }, true);
        if (jWindow.solarSystemID == _json.solarSystemID) {            
            jWindow.drawSigWindow();
        }
    },
    returnSignatures: function (_solarSystemID, _tabID) {
        
        var tab = jMap.returnOpenTab(_tabID);
        if (tab !== false) {
            var groupID = tab.groupID;
            var signatures =  $.grep(jMap.Signatures, function (e) {
                return e.solarSystemID == _solarSystemID && e.groupID == groupID;
            });            
            return signatures;
        } else {
            console.log("Tab " + _tabID + " doesn't exist!");
        }
    },
    addIntel: function (_json) {        
        var intel = new jIntel(_json);
        var oldIntel = $.grep(jMap.Intels, function (e) {
            return e.id == intel.id;
        })
        if (oldIntel.length === 0) {
            //intel is new, add it
            jMap.Intels.push(intel);
            jSystemBox.drawIntel();
            var mapsystem = jMap.returnMapSystem(intel.solarSystemID);
            if (mapsystem !== false) {
                mapsystem.updateBorder();
            }
        }
    },
    deleteIntel: function (_json) {
        var id = _json.id;
        var intel = $.grep(jMap.Intels, function (e) {
            return e.id == id;
        });
        var mapsystem = jMap.returnMapSystem(intel[0].solarSystemID);
        jMap.Intels = $.grep(jMap.Intels, function (e) {
            return e.id == id;
        }, true);
        jSystemBox.drawIntel();
        if (mapsystem !== false) {
            mapsystem.updateBorder();
        }
    },
    addPOI: function (_json) {
        var poi = new jPOI(_json);        
        var oldpoi = $.grep(jMap.POIs, function (e) {
            return ((e.id == poi.id) && (e.solarSystemID == poi.solarSystemID))
        });
        if (oldpoi.length === 0) {
            jMap.POIs.push(poi);
            jSystemBox.drawSysInfo();
            jWindow.drawPOIWindow();
            var mapsystem = jMap.returnMapSystem(poi.solarSystemID);
            if (mapsystem !== false) {
                mapsystem.updateBorder();
            }
        }
    },
    deletePOI: function (_json) {
        var id = _json.id;
        var poi = $.grep(jMap.POIs, function (e) {
            return e.id == id;
        });
        jMap.POIs = $.grep(jMap.POIs, function (e) {
            return e.id == id;
        }, true);
        jSystemBox.drawSysInfo();
        jWindow.drawPOIWindow();
        $.each(poi, function (index, p) {
            var mapsystem = jMap.returnMapSystem(p.solarSystemID);
            if (mapsystem !== false) {
                mapsystem.updateBorder();
            }
        })

    },
    /**
     *      
     * @returns {jMapSystem}
     */
    returnMapSystem: function (_solarSystemID) {
        if (_solarSystemID == 0) {
            return false;
        }
        var returnsystem = false;
        $.each(jMap.MapTabs, function (index, tab) {
            var system = tab.returnMapSystem(_solarSystemID);
            if (system !== false) {
                returnsystem = system;
            }
        });
        return returnsystem;
    },
    returnPOIs: function (_solarSystemID) {
        return $.grep(jMap.POIs, function (e) {
            return e.solarSystemID == _solarSystemID;
        });
    },
    findConnectedTab: function(_solarSystemID,_tabID) {
        $.each(jMap.MapTabs, function (index, tab) {
            var system = tab.returnMapSystem(_solarSystemID);
            if (system !== false && tab.tabID != _tabID) {                
                tab.glow(true);
            }
        });
    },
    findConnectedSystems: function(_tabID) {
        var connectTab = jMap.returnOpenTab(_tabID);
        var currentTab = jMap.returnOpenTab(vGlobals.activeTabID);
        
        if (connectTab==false || currentTab==false || _tabID==vGlobals.activeTabID) {
            return false;
        }
        
        $.each(currentTab.MapSystems, function(index,currentTabSystem) {
            $.each(connectTab.MapSystems, function(index,connectTabSystem) {
                if (connectTabSystem.solarSystemID == currentTabSystem.solarSystemID) {
                    currentTabSystem.glow(true);
                }
            });
        });
        
    }
};

/**
 * handles messages send from the server
 * @type type
 */
var jMessageHandler = {
    handle: function (messages) {
        jQuery.each(messages, function (index, message) {

            //update last message id if the message's id is bigger
            jServer.updateTransactionID(parseInt(message.l));
            //check which action to perform
            switch (message.a) {
                case 'loadcharacters':
                    jWindow.drawAccountWindow(message.d);
                    break;
                case 'addGroups':                    
                    jGroupSelector.addGroups(message.d);
                    break;
                case 'addWormholes':
                    jWormholeList.init(message.d);
                    break;
                case 'addEffects':
                    jEffectList.init(message.d);
                    break;
                case 'addAllowedTabs':
                    jTabSelector.addTabs(message.d);
                    break;
                case 'openTab':                    
                    jMap.addTab(message.d); //tabID,tabName,tabPermission,ownerAccountID
                    break;
                case 'addSystem':                               
                    jMap.addSystem(message.d);
                    break;
                case 'addConnection':                   
                    jMap.addConnection(message.d);
                    break;
                case 'addIntel':                    
                    jMap.addIntel(message.d);
                    break;
                case 'setMessageID':
                    //set last messageID
                    jServer.lastMessageID = message.d.id;
                    break;
                case 'addPoi':                    
                    jMap.addPOI(message.d);
                    break;
                case 'addSig':                    
                    jMap.addSignature(message.d);
                    break;                
                case 'deleteSystem':                    
                    jMap.deleteSystem(message.d);
                    break;
                case 'moveSystem':
                    jMap.dragSystem(message.d);
                    break;
                case 'setLabel':                    
                    jMap.setSystemLabel(message.d);
                    break;   
                case 'closeTab':                    
                    jMap.closeTab(message.d.tabID);
                    jTabSelector.redraw();
                    break;
                case 'conMassStage':
                    //set connection mass stage
                    jMap.setConnectionMassStage(message.d);
                    break;
                case 'conTimeStage':
                    //set connection time stage
                    jMap.setConnectionTimeStage(message.d);
                    break;
                case 'addPilot':                    
                    jMap.addPilot(message.d);
                    break;
                case 'deletePilot':                   
                    jMap.offlineCharacter(message.d.characterName);
                    break;
                case 'deleteSig':                    
                    jMap.deleteSignature(message.d);
                    break;
                case 'deletePoi':                    
                    jMap.deletePOI(message.d);
                    break;
                case 'deleteIntel':                    
                    jMap.deleteIntel(message.d);
                    break; 
                case 'foundexits':
                    jWindow.drawExitWindow(message.d);
                    break;                                                               
                case 'opensigwindow':
                    jWindow.showSigWindow(message.d.solarSystemID, message.d.tabID);
                    break;
                case 'setSystemStatus':                    
                    jMap.setSystemStatus(message.d);
                    break;    
                case 'spssi':
                    //set pilot solarsystem id                    
                    jMap.setPilotSolarSystemID(message.d.solarSystemID);                    
                    break;
                case 'renameTab':                    
                    jMap.renameTab(message.d, message.d.tabID);
                    break;
                 case 'deleteTab':                    
                    jMap.closeTab(message.d.tabID);
                    jTabSelector.removeTab(message.d.tabID);
                    break;   
                case 'sat':
                    //set active tab
                    var openTab = jMap.returnOpenTab(message.d.ti);
                    if (openTab != false) {
                        openTab.makeActive();
                    }
                    break;
                case 'ct':
                    //created tab (add tab to tab selector)                        
                    jTabSelector.addTab(message.d);
                    break;                                                                                                                                     
                case 'conAddMass':
                    //add connection mass
                    jMap.addConnectionMass(message.d);
                    break;               
                case 'deleteConnection':
                    //delete connection
                    jMap.deleteConnection(message.d);
                    break;                
                case 'error':                    
                    jError.show('Server Error', message.d.message);
                    break;
                case 'sync':
                    //setclockoffset
                    jServer.syncClock(message.d.offset);
                    break;
            }
            try {
            }

            catch (e) {
                console.log(e);
            }
        });
    }
};

/**
 * handles communication with the server
 
 * @type type */
var jServer = {
    lastMessageID: -1,
    serverRequests: new Array(),
    callsDone: true,
    clockOffset: 0,
    /**
     * sends a message to the server and calls the message handler on success;
     * waits for the previous ajax request to finish or timeout before sending the next
     * @param {string} _url ajax/url
     * @param {array} _message array containing jMessages
     * @param {callback} _complete function that gets called once completed     
     */
    sendRequest: function (_url, _message, _complete) {
        jServer.serverRequests.push(new jServerRequest(_url, _message, _complete));
        if (jServer.callsDone) {
            jServer.ajaxRequest();
        }
    },
    updateTransactionID: function (_transactionID) {
        if (_transactionID > this.lastMessageID) {
            this.lastMessageID = _transactionID;
        }
    },
    pullTab: function (_tabID) {
        //make the ajaxrequest
        var messageArray = new Array(new jMessage('openTab', {tabID: _tabID}));
        jServer.sendRequest('request.php', messageArray);

    },
    ajaxRequest: function () {
        jServer.callsDone = false;
        var request = jServer.serverRequests.shift();
        if (typeof (request) === 'undefined') {
            jServer.callsDone = true;
            jLoop.resetLoop();
        } else {
            var url = './server/ajax/' + request.url;
            //encode variables and array
            var _messageJSON = JSON.stringify(request.message);
            $.ajax({
                timeout: vConstants.timeoutTime,
                url: url,
                type: 'POST',
                dataType: 'json',
                //headers: { "Accept-Encoding" : "gzip" },
                data: {v: vConstants.clientVersion, m: _messageJSON, l: jServer.lastMessageID, t: vGlobals.activeTabID},
                success: function (json) {
                    jError.clear();
                    jMessageHandler.handle(json.m);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jError.show(errorThrown);
                },
                complete: function () {
                    if (typeof (request.complete) === 'function') {
                        request.complete();
                    }
                    jServer.ajaxRequest();
                }
            });
        }

    },
    syncClock: function (_serverTime) {
        var clientTime = Math.round(new Date().getTime() / 1000);
        jServer.clockOffset = parseInt(_serverTime) - clientTime;
        console.log('set clock offset to ' + jServer.clockOffset + 's');
    }
};

/**
 * handles errors
 */
var jError = {
    show: function (_err, _errmsg) {
        var buffer = "<h1><font color=red>" + _err + "</font></h1> <b>" + _errmsg + "</b><p/>";
        $("#errormessage").html(buffer);
        $("#errorscreen").show();
        console.log(_err);
    },
    clear: function () {
        $("#errorscreen").hide();
    }
};

/**
 * handles the loop pinging the server
 */
var jLoop = {
    pingFinished: true,
    interval: null,
    startLoop: function () {
        jLoop.interval = setInterval(function () {
            jLoop.pingServer();
        }, vConstants.loopTime);
    },
    stopLoop: function () {
        if (jLoop.interval !== null) {
            clearInterval(jLoop.interval);
        }
    },
    resetLoop: function () {
        jLoop.stopLoop();
        StartTimer();
        jLoop.startLoop();
    },
    pingServer: function () {
        //check if the previous ping is finished, if not don't ping again
        //also don't ping while we are dragging systems around
        if (((jLoop.pingFinished) && (!vGlobals.dragging))) {
            //restart timer
            StartTimer();
            //do the ajax request
            jLoop.pingFinished = false;
            jServer.sendRequest('request.php', new Array({lmid: jServer.lastMessageID,
                atbi: vGlobals.activeTabID}), function () {
                //ajax call succeeded
                jLoop.pingFinished = true;
            }
            );

        }
    }
};

var jPaste = {
    handle: function (_paste) {        
        if (_paste === '') { //don't bother with empty pastes
            return false;
        }
        //split up lines
        var pasteLines = _paste.split("\n");

        //check if it's a Signature paste
        var regexp = /([A-Z]{3}\-[0-9]{3})\s{1}([a-zA-z ]*)/;
        var match = null;
        match = regexp.exec(pasteLines[0]);
        if (match !== null) {
            if ((jUtils.localize(match[2]) === 'Cosmic Signature') || (jUtils.localize(match[2]) === 'Cosmic Anomaly')) {
                //is a Sig paste
                jPaste.sigPaste(pasteLines);
            }
        }
    },
    sigPaste: function (_pasteLines) {                
        var regexp = /([A-Z]{3}\-[0-9]{3})\s{1}([a-zA-z ]*)\s{1}([a-zA-z ]*)\s{1}([a-zA-z ]*)\s{1}([0-9]*\.?[0-9]+)/;
        var parsedResult = new Array();
        $.each(_pasteLines, function (index, line) {
            var match = null;
            match = regexp.exec(line);
            if (match !== null) {
                parsedResult.push({
                    sig: match[1],
                    isanom: jUtils.localize(match[2]) === 'Cosmic Anomaly' ? true : false,
                    type: match[3],
                    detail: match[4],
                    strenght: parseFloat(match[5].replace(',', '.'))
                });
            }
        });
        var solarSystemID = vGlobals.pilotSolarSystemID;
        if ($('#signaturewindow').is(":visible")) {
                solarSystemID = jWindow.solarSystemID;                
            }
        if (solarSystemID == 0) {
            if ($('#signaturewindow').is(":visible")) {
                solarSystemID = jWindow.solarSystemID;                
            } else {
                return false;
            }
        }      
        jUI.pasteSigs(parsedResult, solarSystemID, vGlobals.activeTabID);
    }
};

function InitMap() {
    //jWormholeList.init();
    //jEffectList.init();
    jServer.sendRequest('init.php', new Array(new jMessage('Init')));
    jMap.drawEmptyTab();
    //resize the map to active window size
    Resize();
}

//MAIN FUNCTION
function Init(_characterName) {
    //set character Name
    vGlobals.characterName = _characterName;

    //jsPlumb.setRenderMode(jsPlumb.SVG);
    jsPlumb.ready(function () {
        jQuery(document).ready(function ($) {
            //catch left click
            $('.mapborder').mousedown(function () {
                //hide context menu on left click
                jContext.hide();
                $('#catchpaste').focus();
            });
            $(document).bind('contextmenu', function (event) {
                event.preventDefault();
            });

            //catch pastes
            $(document).keydown(function (event) {
                if (event.ctrlKey && event.keyCode == 0x56) {

                    if (($(event.target)[0].type !== 'text') && ($(event.target)[0].type !== 'textarea' || event.target.id === 'catchpaste')) {
                        $('#catchpaste').focus();
                        setTimeout(function () {
                            jPaste.handle($('#catchpaste').val());
                            //console.log($('#catchpaste').val());
                            $('#catchpaste').val('');
                        });
                    }

                }
            });
            //move mouseover
            $(document).mousemove(function (event) {
                $('.mouseover').css({position: 'absolute', top: event.pageY + 7, left: event.pageX + 7});
            });
            //Initialize stuff
            InitMap();
            InitTimer();
            StartTimer();
            $('.window').draggable();
            //start timer for pinging the server
            setInterval(function () {
                jLoop.pingServer();
            }, vConstants.loopTime);
        });
    });
}
;

jUtils = {
    localize: function (_text) {
        switch (_text) {
            case 'Kosmische Signatur':
                return 'Cosmic Signature';
            case 'Kosmische Anomalie':
                return 'Cosmic Anomaly';
            default:
                return _text;
        }
    },
    returnJumpString: function (_safe, _unsafe) {
        if (_unsafe == -1) {
            return '-';
        } else
        if (_safe == -1) {
            return '<font color=red>' + _unsafe + '</font>';
        } else
        if (parseInt(_safe) <= parseInt(_unsafe)) {
            return '<font color=lightgreen>' + _safe + '</font>';
        } else {
            return '<font color=lightgreen>' + _safe + '</font>|<font color=red>' + _unsafe + '</font>';
        }

    }
};

//HELPER FUNCTIONS
function InitTimer() {
    $('.timer').asPieProgress({
        'namespace': 'pie_progress',
        'barsize': '3',
        'size': '14',
        'barcolor': '#BBB',
        'trackcolor': '#666',
        'speed': vConstants.loopTime / 100
    });
}
function StartTimer() {
    $('.timer').asPieProgress('reset');
    $('.timer').asPieProgress('start');
}
function Resize() {
    var w = $(window).width() - 20;
    var h = $(window).height() - 160;
    $(".mapborder").css({width: w - 42, height: h - 42});
    $("#topbar").css({width: w + 8});
}
function ConnectionRepaint(_endpoint, _divID) {
    //first repaint the endpoint
    _endpoint.repaint();
    //then find all connections and leading from and to the system and repaint them
    var sourceConn = jsPlumb.getConnections({source: _divID});
    for (var b = 0; b < sourceConn.length; b++)
    {
        if (sourceConn[b] != undefined) {
            sourceConn[b].repaint();
        }
    }
    var targetConn = jsPlumb.getConnections({target: _divID});
    for (var b = 0; b < targetConn.length; b++)
    {
        if (targetConn[b] != undefined) {
            targetConn[b].repaint();
        }
    }
}

function getClassLabel(_class) {
    if (typeof (_class) == 'undefined') {
        return '';
    }
    _class = parseInt(_class);
    switch (_class) {
        case (1):
            return 'C1';
            break;
        case (2):
            return 'C2';
            break;
        case (3):
            return 'C3';
            break;
        case (4):
            return 'C4';
            break;
        case (5):
            return 'C5';
            break;
        case (6):
            return 'C6';
            break;
        case (7):
            return 'HS';
            break;
        case (8):
            return 'LS';
            break;
        case (9):
            return 'NS';
            break;
        case (31):
            return 'C1';
            break;
        case (32):
            return 'C2';
            break;
        case (33):
            return 'C3';
            break;
        case (34):
            return 'C4';
            break;
        case (35):
            return 'C5';
            break;
        case (36):
            return 'C6';
            break;
        case (40):
            return 'TH';
            break;
        case (41):
            return 'C13';
            break;
        case (42):
            return 'C13';
            break;
        case (43):
            return 'C13';
            break;
        default :
            return _class;
            break;
    }
}
function getPrettyClassLabel(_class) {
    if (typeof (_class) == 'undefined') {
        return '';
    }
    _class = parseInt(_class);
    switch (_class) {
        case (1):
            return 'Class 1';
            break;
        case (2):
            return 'Class 2';
            break;
        case (3):
            return 'Class 3';
            break;
        case (4):
            return 'Class 4';
            break;
        case (5):
            return 'Class 5';
            break;
        case (6):
            return 'Class 6';
            break;
        case (7):
            return 'High-Sec';
            break;
        case (8):
            return 'Low-Sec';
            break;
        case (9):
            return 'Null-Sec';
            break;
        case (31):
            return 'Shattered Class 1';
            break;
        case (32):
            return 'Shattered Class 2';
            break;
        case (33):
            return 'Shattered Class 3';
            break;
        case (34):
            return 'Shattered Class 4';
            break;
        case (35):
            return 'Shattered Class 5';
            break;
        case (36):
            return 'Shattered Class 6';
            break;
        case (40):
            return 'Thera';
            break;
        case (41):
            return 'Frig-System';
            break;
        case (42):
            return 'Frig-System';
            break;
        case (43):
            return 'Frig-System';
            break;
        default :
            return _class;
            break;
    }
}
function getPrettyEffect(_effect) {
    switch (_effect) {
        case '':
            return '';
            break;
        case 'wr':
            return 'Wolf-Rayet Star';
            break
        case 'bh':
            return 'Black Hole';
            break
        case 'cv':
            return 'Cataclysmic Variable';
            break
        case 'pulsar':
            return 'Pulsar';
            break
        case 'rg':
            return 'Red Giant';
            break
        case 'mag':
            return 'Magnetar';
            break

    }
}
function fDiv(n1, n2) {
    return (n1 - (n1 % n2)) / n2;
}
function MakeTime(t) {
    var output = "";
    if (t > 0) {
        var dt = new Date().getTime() - (t * 1000);
        var hours = fDiv(dt, 3600000);
        dt %= 3600000;
        var minutes = fDiv(dt, 60000);
        var minutes = ((minutes < 10) ? "0" : "") + minutes + "h";
        output += hours + ":" + minutes;
    } else
        output += "-";
    return output;
}
function numberFormat(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
$.fn.selectRange = function (start, end) {
    var e = document.getElementById($(this).attr('id'));
    if (!e)
        return;
    else if (e.setSelectionRange) {
        e.focus();
        e.setSelectionRange(start, end);
    } /* WebKit */
    else if (e.createTextRange) {
        var range = e.createTextRange();
        range.collapse(true);
        range.moveEnd('character', end);
        range.moveStart('character', start);
        range.select();
    } /* IE */
    else if (e.selectionStart) {
        e.selectionStart = start;
        e.selectionEnd = end;
    }
};
$(window).resize(function () {
    var w = $(window).width() - 20;
    var h = $(window).height() - 160;
    $(".mapborder").css({width: w - 60, height: h - 60});
    $("#topbar").css({width: w - 10});
});
$.ui.autocomplete.filter = function (array, term) {
    var matcher = new RegExp("^" + $.ui.autocomplete.escapeRegex(term), "i");
    return $.grep(array, function (value) {
        return matcher.test(value.label || value.value || value);
    });
};
