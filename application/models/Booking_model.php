<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Booking_model (Booking Model)
 * Booking model class to get to handle booking related data 
 * @author : uhc@icddrb.org
 * @version : 1.5
 * @since : 18 Jun 2022
 */
class Booking_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function bookingListingCount($searchText)
    {
        $this->db->select('BaseTbl.pId, BaseTbl.zil_Name, BaseTbl.upz_Name, BaseTbl.uni_Name, BaseTbl.war_Name, BaseTbl.sc_Type, BaseTbl.sp_d, BaseTbl.sp_Name, BaseTbl.int_dt, BaseTbl.pName, BaseTbl.fName, BaseTbl.mName, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_booking as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.pName LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $query = $this->db->get();
        
        return $query->num_rows();
    }
    
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function bookingListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.pId, BaseTbl.zil_Name, BaseTbl.upz_Name, BaseTbl.uni_Name, BaseTbl.war_Name, BaseTbl.sc_Type, BaseTbl.sp_d, BaseTbl.sp_Name, BaseTbl.int_dt, BaseTbl.pName, BaseTbl.fName, BaseTbl.mName, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_booking as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.pName LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.pId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }
    
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewBooking($bookingInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_booking', $bookingInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $pId : This is booking id
     * @return array $result : This is booking information
     */
    function getBookingInfo($pId)
    {
        $this->db->select('pId, zil_Name, upz_Name, uni_Name, war_Name, sc_Type, sp_d, sp_Name, int_dt, pName, fName, mName, description');
        $this->db->from('tbl_booking');
        $this->db->where('pId', $pId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $pId : This is booking id
     */
    function editBooking($bookingInfo, $pId)
    {
        $this->db->where('pId', $pId);
        $this->db->update('tbl_booking', $bookingInfo);
        
        return TRUE;
    }
}