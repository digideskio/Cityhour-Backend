<?php
include_once 'Common.class.php';

class UpdateOne extends Common {

    /** @var int $q_s Query time start */
    /** @var int $q_e Query time end */
    var $q_s;
    var $q_e;

    public function __construct($debug) {
        $this->debug = $debug;
        $this->start();
        $this->q_s = strtotime(date('m/d/Y', time()));
        $this->q_e = strtotime('+16 days', $this->q_s);
    }

    public function getAllData($id) {
        $allData = $this->getSlots($id);
        $allData = array_merge($allData, $this->getFreeSlots($id));
        return $allData;
    }

    public function makeMagic($AllData) {
        // Free slots not cross Meet slots
        $rSlots = $this->findCrossOrNot($AllData,1,2,false);

        // Free slots cross Meet slots
        $slots = $this->findCrossOrNot($AllData,1,2,true);
        $slots = $this->sortById($slots);
        $rSlots = array_merge($rSlots,$this->addOrRemoveTime($slots,false));

        if ($AllData[0]['is_free'] == 0) {
            return $rSlots;
        }

        // Busy slots not cross Free slots
        $bSlots = $this->getSlotsByType($AllData,0);
        $slots = array_merge($bSlots,$rSlots);
        $zSlots = $this->findCrossOrNot($slots,0,1,false);

        // Busy slots cross Free slots
        $slots = $this->findCrossOrNot($slots,0,1,true);
        $slots = $this->sortById($slots);
        $zSlots = array_merge($zSlots,$this->addOrRemoveTime($slots,false));

        // Busy slots plus Meet slots
        //Not cross
        $mSlots = $this->getSlotsByType($AllData,2);
        $slots = array_merge($mSlots,$zSlots);
        $xSlots = $this->findCrossOrNot($slots,2,0,false);
        $xSlots = array_merge($xSlots,$this->findCrossOrNot($slots,0,2,false));

        //Cross
        $slots = $this->findCrossOrNot($slots,2,0,true);
        $slots = $this->sortById($slots);
        $xSlots = array_merge($xSlots,$this->addOrRemoveTime($slots,true));

        // Make look busy slots like Meet slots
        $xSlots = $this->setSlotsType($xSlots,2);

        //All freetime slots not cross Meet+Busy slots
        $slots = $this->getSlotsByType($AllData,3);
        $slots = array_merge($slots,$xSlots);
        $rSlots = array_merge($rSlots,$this->findCrossOrNot($slots,3,2,false));

        //All freetime slots cross Meet+Busy slots
        $slots = $this->findCrossOrNot($slots,3,2,true);
        $slots = $this->sortById($slots);
        $rSlots = array_merge($rSlots,$this->addOrRemoveTime($slots,false));

        return $rSlots;
    }

    public function storeOneMagic($magicSlots,$id) {
        if ($magicSlots) {
            $this->db->startTransaction();
            try {
                $this->query("delete from free_slots where user_id = $id");
                $this->insertIntoFree($magicSlots,'free_slots');

                $this->db->commit();
                return true;
            }
            catch (Exception $e) {
                $this->db->rollBack();
                return false;
            }
        }
        else {
            return true;
        }
    }

}