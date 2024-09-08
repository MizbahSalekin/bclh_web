<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Report_model extends CI_Model
{
    function getDivision(){

        $query = "SELECT DISTINCT divisioneng FROM division where upload = 1 ORDER BY divisioneng ASC";

        $division = $this->db->query($query);
        return $division->result();
    }

    function getDistrict(){

        $query = "SELECT DISTINCT zillanameeng, zillaid FROM zilla where upload = 1 ORDER BY zillanameeng ASC";

        $district = $this->db->query($query);
        return $district->result();
    }

    function getUpazilla(){

        $query = "SELECT DISTINCT upazilanameeng, upazilaid FROM upazila where upload = 1 ORDER BY upazilanameeng ASC";

        $upaz = $this->db->query($query);
        return $upaz->result();
    }

    function getUnion(){

        $query = "SELECT DISTINCT unionnameeng FROM unions where upload = 1  ORDER BY unionnameeng ASC";

        $uni = $this->db->query($query);
        return $uni->result();
    }

    function eScreening_model()
    {
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $zilla_id = $this->input->post('zilla_id'); 
        $upazila_id = $this->input->post('upazila_id');
        $union_id = $this->input->post('union_id');
        $where = '';
        if($zilla_id != ''){
            $where .= "AND s1.zillaid = '" . $zilla_id . "'";
          
        }
        if($upazila_id != ''){
            $where .= "AND s1.upazilaid = '" . $upazila_id . "'";
            
        }

        if($union_id != ''){
            $where .= "AND s1.unionid = '" . $union_id . "'";
            
        }

        if($start_date != ''){
            $where .= "AND DATE(s1.uploaddt) >= '" . $start_date . "'";
          
        }
        if($end_date != ''){
            $where .= "AND DATE(s1.uploaddt) <= '" . $end_date . "'";
            
        }

        $queryRadio = "SELECT
                        (IF(s2.q212 IN (1, 2), 'Yes', 'No')) AS Vaccinated,
                        (CASE
                            WHEN s2.q205b = 2 
                                AND s2.q203 = 2 
                                AND (s1.q109 NOT LIKE '%test%' 
                                OR s2.q201 NOT LIKE '%test%' 
                                OR s2.q206a NOT LIKE '%test%' 
                                OR s2.q206b NOT LIKE '%test%') 
                            THEN 'Yes' 
                            ELSE 'No'
                        END) AS 'Zero-dose',
                        CASE
                            WHEN s2.q203 = 2
                                AND s2.q205b = 1
                                AND s2.q205c = 2
                                AND s2.q205d = 2
                                AND (
                                    (s1.q109 NOT LIKE '%test%' OR s1.q109 IS NULL)
                                    OR (s2.q201 NOT LIKE '%test%' OR s2.q201 IS NULL)
                                    OR (s2.q206a NOT LIKE '%test%' OR s2.q206a IS NULL)
                                    OR (s2.q206b NOT LIKE '%test%' OR s2.q206b IS NULL)
                                    )
                                THEN 'Yes'
                                ELSE 'No'
                        END AS 'Under-immunized',
                        (CASE
                            WHEN s2.q203 = 1
                                OR (s2.q205b = 1 AND s2.q205c = 1 AND s2.q205d = 1)
                                AND ((s1.q109 NOT LIKE '%test%' OR s1.q109 IS NULL)
                                OR (s2.q201 NOT LIKE '%test%' OR s2.q201 NOT LIKE 'E' OR s2.q201 NOT LIKE '%Rgy%' OR s2.q201 IS NULL)
                                OR (s2.q206a NOT LIKE '%test%' OR s2.q206a IS NULL)
                                OR (s2.q206b NOT LIKE '%test%' OR s2.q206b IS NULL))
                            THEN 'Yes' 
                            ELSE 'No'
                        END) AS 'Drop-out',

                        z.zillanameeng AS 'District',
                        u.upazilanameeng AS 'Upazilla',
                        un.unionnameeng AS 'Union',
                        c.ward_no AS 'Ward_No',
                        c.epi_sub_block AS 'EPI_sub_block',
                        c.epi_cluster_name AS 'EPI_cluster',
                        p.provname AS 'Provider_name',    
                        CASE
                            WHEN s1.q106 = 1 THEN 'Upazilla_Health_Complex'
                            WHEN s1.q106 = 2 THEN 'Mother and Child Welfare Centre'
                            WHEN s1.q106 = 3 THEN 'Union Health and Family Welfare Center/Union Sub-Centre'
                            WHEN s1.q106 = 4 THEN 'Community Clinic'
                            ELSE 'Others'
                            END AS 'Service_center',
                        pt.typename as 'Provider_designation',
                        DATE_FORMAT(s1.q108, '%d-%m-%Y') AS 'Interview_date',
                        s1.q109 AS 'Caregiver_name',
                        s2.idno AS 'Registration_ID',
                        s2.q201 AS 'Child_name',
                        s2.q206a AS 'Mother_name',
                        s2.q206b AS 'Father_name',
                        s2.q206c AS 'Mobile_no',
                        s2.q206d AS 'House_name',
                        s2.q202 AS 'DOB',
                        CASE
                            WHEN s2.q202a = 1 THEN 'Yes'
                            WHEN s2.q202a = 2 THEN 'No'
                            ELSE 'Not_Selected'
                            END AS 'Unknown_DOB',
                        s2.q202b1 AS 'Age_(Day)',
                        s2.q202b2 AS 'Age_(Month)',
                        s2.q202b3 AS 'Age_(Year)',
                        CASE
                            WHEN s2.q203 = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Received_all_vaccine',
                        CASE
                            WHEN s2.q204a = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Not_time_to_vaccinate',
                        CASE
                            WHEN s2.q204b = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Fear_of_side_effects',
                        CASE
                            WHEN s2.q204c = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Child_ilness',
                        CASE
                            WHEN s2.q204d = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Parent_business',
                        CASE
                            WHEN s2.q204e = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Not_permitted',
                        CASE
                            WHEN s2.q204f = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Financial_issue',
                        CASE
                            WHEN s2.q204g = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Vaccine_center_located_far',
                        CASE
                            WHEN s2.q204h = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Thought_to_vaccinate',
                        CASE
                            WHEN s2.q204i = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'No_faith_in_vaccine',
                        CASE
                            WHEN s2.q204j = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Inconvenient_hours',
                        CASE
                            WHEN s2.q204k = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Unknown_center',
                        CASE
                            WHEN s2.q204l = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Unsatisfied_vaccinator_behaviour',
                        CASE
                            WHEN s2.q204x = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Other_reasons',
                        s2.q204x1 AS 'Specify_reason',
                        CASE
                            WHEN s2.q205a = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS BCG,
                        CASE
                            WHEN s2.q205b = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Penta-1',
                        CASE
                            WHEN s2.q205c = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Penta-2',
                        CASE
                            WHEN s2.q205d = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Penta-3',
                        CASE
                            WHEN s2.q205e = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'MR-1',
                        CASE
                            WHEN s2.q205f = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'MR-2',
                        CASE
                            WHEN s2.q205x = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS Others,
                        CASE
                            WHEN s2.q207 = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Selected_for_information',
                        u.upazilanameeng AS 'EPI_Upazilla',
                        un.unionnameeng AS 'EPI_Union',
                        c.ward_no AS 'EPI_Ward_no',
                        c.epi_cluster_name AS 'EPI_Cluster',

                        CASE
                            WHEN s2.q212 = 1 THEN 'Vaccinated_all_doses'
                            WHEN s2.q212 = 2 THEN 'Partially_vaccinated'
                            WHEN s2.q212 = 3 THEN 'In_progress'
                            ELSE 'UnSynced'
                            END AS 'Vaccination_status',
                        CASE
                            WHEN s2.q212a = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Child_not_found',
                        CASE
                            WHEN s2.q212b = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Migrated',
                        CASE
                            WHEN s2.q212c = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Child_illness',
                        CASE
                            WHEN s2.q212d = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Parent_business',
                        CASE
                            WHEN s2.q212e = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Caregiver_not_interested',
                        CASE
                            WHEN s2.q212f = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Not_time_to_vaccinate',
                        CASE
                            WHEN s2.q212g = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Financial_issue',
                         CASE
                            WHEN s2.q212h = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Caregivcer_illness',
                        CASE
                            WHEN s2.q212x = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Other_resason',
                        CASE
                            WHEN s2.q212x = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Specify_provider_reason',
                        DATE_FORMAT(s2.q213, '%d-%m-%Y') AS 'Vaccine_info_date',
                        DATE_FORMAT(s1.uploaddt, '%d-%m-%Y') AS 'Upload_date'
                        FROM
                        section_1_screening_checklist_idf s1 
                        JOIN section_2_vaccinations_info s2 
                            ON s2.idno = s1.idno 
                        LEFT JOIN providerdb p 
                            ON p.providerid = CAST(s1.entryuser AS CHAR) 
                        LEFT JOIN providertype pt 
                            ON pt.provtype = CAST(s1.q107 AS CHAR) 
                        LEFT JOIN zilla z 
                            ON z.zillaid = s1.zillaid 
                        LEFT JOIN upazila u 
                            ON u.zillaid = s1.zillaid 
                            AND u.upazilaid = s1.upazilaid 
                        LEFT JOIN unions un 
                            ON un.zillaid = s1.zillaid 
                            AND un.upazilaid = s1.upazilaid 
                            AND un.unionid = s1.unionid 
                        LEFT JOIN cluster c 
                            ON c.clusterid = CAST(s2.q211 AS CHAR) 
                        WHERE s2.idno IS NOT NULL $where;
                    ";

        $radio_query_result = $this->db->query($queryRadio);
        return $radio_query_result->result();
    }
    function eSupervision_model()
    {
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $upazila_id = $this->input->post('upazila_id'); 
        $union_id = $this->input->post('union_id');
        $where = '';
        if($upazila_id != ''){
            $where .= "AND s1.upazilaid = '" . $upazila_id . "'";
          
        }
        if($union_id != ''){
            $where .= "AND s1.unionid = '" . $union_id . "'";
            
        }

        if($start_date != ''){
            $where .= "AND DATE(s1.uploaddt) >= '" . $start_date . "'";
          
        }
        if($end_date != ''){
            $where .= "AND DATE(s1.uploaddt) <= '" . $end_date . "'";
            
        }

        $queryRadio = "SELECT
                        s2.idno AS 'Registration_ID',
                        z.zillanameeng AS 'District',
                        u.upazilanameeng AS 'Upazilla',
                        un.unionnameeng AS 'Union',
                        c.ward_no AS 'Ward_No',
                        c.epi_sub_block AS 'EPI_sub_block',
                        c.epi_cluster_name AS 'EPI_cluster',
                        p.provname AS 'Provider_name',
                        pt.typename AS 'Provider_designation',
                        DATE_FORMAT(s1.interviewer_date, '%d-%m-%Y') AS 'Interview_date',
                        s1.q105 AS 'Ward_no',
                        CASE
                            WHEN s2.q111 = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Observed_session',
                        CASE
                            WHEN s2.q111a = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Child_illness',
                        CASE
                            WHEN s2.q111b = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Not_at_home',
                        CASE
                            WHEN s2.q111c = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Migrated',
                        CASE
                            WHEN s2.q111d = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Parent_illness',
                        CASE
                            WHEN s2.q111e = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Undetermined_cause',
                        CASE
                            WHEN s2.q111x = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Other_Reasons',
                        s2.q111x1 AS 'Specify_Reason',
                        CASE
                            WHEN s2.q112a = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'BCG',
                        s2.q112a1 AS 'Target_BCG',
                        s2.q112a2 AS 'Achieved_BCG',
                        CASE
                            WHEN s2.q112b = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Penta-1',
                        s2.q112b1 AS 'Target_Penta-1',
                        s2.q112b2 AS 'Achieved_Penta-1',
                        CASE
                            WHEN s2.q112c = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Penta-2',
                        s2.q112c1 AS 'Target_Penta-2',
                        s2.q112c2 AS 'Achieved_Penta-2',
                        CASE
                            WHEN s2.q112d = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Penta-3',
                        s2.q112d1 AS 'Target_Penta-3',
                        s2.q112d2 AS 'Achieved_Penta-3',

                        -- CASE
                        --     WHEN s2.q112e = 1 THEN 'Yes'
                        --     ELSE 'No'
                        --     END AS PCV_1,
                        -- s2.q112e1 AS 'Target_PCV-1',
                        -- s2.q112e2 AS 'Achieved_PCV-1',
                        -- CASE
                        --     WHEN s2.q112f = 1 THEN 'Yes'
                        --     ELSE 'No'
                        --     END AS PCV_2,
                        -- s2.q112f1 AS 'Target_PCV-2',
                        -- s2.q112f2 AS 'Achieved_PCV-2',
                        -- CASE
                        --     WHEN s2.q112g = 1 THEN 'Yes'
                        --     ELSE 'No'
                        --     END AS PCV_3,
                        -- s2.q112g1 AS 'Target_PCV-3',
                        -- s2.q112g2 AS 'Achieved_PCV-3',
                        -- CASE
                        --     WHEN s2.q112h = 1 THEN 'Yes'
                        --     ELSE 'No'
                        --     END AS OPV_1,
                        -- s2.q112h1 AS 'Target_OPV-1',
                        -- s2.q112h2 AS 'Achieved_OPV-1',
                        -- CASE
                        --     WHEN s2.q112i = 1 THEN 'Yes'
                        --     ELSE 'No'
                        --     END AS OPV_2,
                        -- s2.q112i1 AS 'Target_OPV-2',
                        -- s2.q112i2 AS 'Achieved_OPV-2',
                        -- CASE
                        --     WHEN s2.q112j = 1 THEN 'Yes'
                        --     ELSE 'No'
                        --     END AS OPV_3,
                        -- s2.q112j1 AS 'Target_OPV-3',
                        -- s2.q112j2 AS 'Achieved_OPV-3',

                        CASE
                            WHEN s2.q112k = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'MR-1',
                        s2.q112k1 AS 'Target_MR_1',
                        s2.q112k2 AS 'Achieved_MR-1',

                        -- CASE
                        --     WHEN s2.q112k = 1 THEN 'Yes'
                        --     ELSE 'No'
                        --     END AS MR_2,
                        -- s2.q112l1 AS 'Target_MR-2',
                        -- s2.q112l2 AS 'Achieved_MR-2',

                        s2.q113 AS 'Remarks',
                        CASE
                            WHEN s2.q112p1a = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Child_illness_P1',
                        CASE
                            WHEN s2.q112p1b = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Not_home_P1',
                        Case
                            WHEN s2.q112p1c = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Migrated_P1',
                        Case
                            WHEN s2.q112p1d = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Parent_illness_P1',
                    -- not in view
                        CASE
                            WHEN s2.q112p1e = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Undetermined_cause_P1',                        
                        CASE
                            WHEN s2.q112p1x = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Other_reasons_P1',
                        s2.q112p1x1 AS 'Specify_reason_P1',       

                        CASE
                            WHEN s2.q112p2a = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Child_ilness_P2',
                        CASE
                            WHEN s2.q112p2b = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Not_at_home_P2',
                        CASE
                            WHEN s2.q112p2c = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Migrated_P2',
                        CASE
                            WHEN s2.q112p2d = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Parent_illness_P2',
                        CASE
                            WHEN s2.q112p2e = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Undetermined_cause_P2',
                        CASE
                            WHEN s2.q112p2x = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Other_reasons_P2',
                        s2.q112p2x1 AS 'Specify_reason_P2',

                        CASE
                            WHEN s2.q112p3a = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Child_ilness_P3',
                        CASE
                            WHEN s2.q112p3b = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Not_at_home_P3',
                        CASE
                            WHEN s2.q112p3c = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Migrated_P3',
                        CASE
                            WHEN s2.q112p3d = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Parent_illness_P3',
                        CASE
                            WHEN s2.q112p3e = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Undetermined_cause_P3',
                        CASE
                            WHEN s2.q112p3x = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Other_reasons_P3',
                        s2.q112p3x1 AS 'Specify_reason_P3',

                        CASE
                            WHEN s2.q112m1a = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Child_ilness_MR-1',
                        CASE
                            WHEN s2.q112m1b = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Not_at_home_MR-1',
                        CASE
                            WHEN s2.q112m1c = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Migrated_MR-1',
                        CASE
                            WHEN s2.q112m1d = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Parent_illness_MR-1',
                        CASE
                            WHEN s2.q112m1e = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Undetermined_cause_MR-1',
                        CASE
                            WHEN s2.q112m1x = 1 THEN 'Yes'
                            ELSE 'No'
                            END AS 'Other_reasons_MR-1',
                        s2.q112m1x1 AS 'Specify_reason_MR-1',

                        s2.q113 AS Remarks,
                        DATE_FORMAT(s1.uploaddt, '%d-%m-%Y') AS Upload_Date 
                        FROM
                        section_1_identifications_ipc_reg s1 
                        JOIN section_1_manager_staff_service s2 
                            ON s2.idno = s1.idno 
                        LEFT JOIN providerdb p 
                            ON p.providerid = CAST(s1.entryuser AS UNSIGNED) 
                        LEFT JOIN providertype pt 
                            ON pt.provtype = CAST(s1.q107 AS UNSIGNED) 
                        LEFT JOIN zilla z 
                            ON z.zillaid = s1.zillaid 
                        LEFT JOIN upazila u 
                            ON u.zillaid = s1.zillaid 
                            AND u.upazilaid = s1.upazilaid 
                        LEFT JOIN unions un 
                            ON un.zillaid = s1.zillaid 
                            AND un.upazilaid = s1.upazilaid 
                            AND un.unionid = s1.unionid 
                        LEFT JOIN cluster c 
                            ON c.clusterid = CAST(s1.q106 AS UNSIGNED) 
                        WHERE s2.idno IS NOT NULL $where;
                    ";

        $radio_query_result = $this->db->query($queryRadio);
        return $radio_query_result->result();
    }
    
    function eScreening_summary_model()
    {
        $queryUnion = "SELECT
                        'Total' AS 'Area',
                        SUM(CASE
                                WHEN s2.q203 = 2
                                    AND s2.q205b = 2
                                    AND s2.q205c = 2
                                    AND s2.q205d = 2
                                    AND (
                                        s1.q109 NOT LIKE '%test%' 
                                        OR s2.q201 NOT LIKE '%test%' 
                                        OR s2.q206a NOT LIKE '%test%' 
                                        OR s2.q206b NOT LIKE '%test%'
                                    ) 
                                THEN 1 
                                ELSE 0
                        END) AS 'Zero-dose',
                        SUM(CASE
                            WHEN s2.q203 = 2
                                AND s2.q205b = 1
                                AND (s2.q205c = 2 OR s2.q205d = 2) 
                                AND (s1.q109 NOT LIKE '%test%'
                                    OR s2.q201 NOT LIKE '%test%'
                                    OR s2.q206a NOT LIKE '%test%'
                                    OR s2.q206b NOT LIKE '%test%'
                                ) 
                            THEN 1 
                            ELSE 0
                        END) AS 'Under-immunized',

                        (COUNT(DISTINCT s1.idno) - 
                        SUM(CASE
                            WHEN s2.q205b = 2 
                                AND s2.q203 = 2 
                                AND (s1.q109 NOT LIKE '%test%' 
                                OR s2.q201 NOT LIKE '%test%' 
                                OR s2.q206a NOT LIKE '%test%' 
                                OR s2.q206b NOT LIKE '%test%') 
                            THEN 1 
                            ELSE 0
                        END) - 
                        SUM(CASE
                            WHEN s2.q203 = 2
                                AND s2.q205b = 1
                                AND s2.q205c = 2
                                AND s2.q205d = 2
                                AND (s1.q109 NOT LIKE '%test%'
                                    OR s2.q201 NOT LIKE '%test%'
                                    OR s2.q206a NOT LIKE '%test%'
                                    OR s2.q206b NOT LIKE '%test%'
                                ) 
                            THEN 1 
                            ELSE 0
                        END) 
                        ) AS 'Drop-out',
                        COUNT(DISTINCT s1.idno) AS 'Total_(ZD+UI)',
                        SUM(IF(s2.q212 IN (1, 2), 1, 0)) AS Vaccinated
                        FROM section_1_screening_checklist_idf s1 
                        JOIN section_2_vaccinations_info s2 
                            ON s2.idno = s1.idno
                        LEFT JOIN providerdb p 
                            ON p.providerid = CAST(s1.entryuser AS CHAR) 
                        LEFT JOIN providertype pt 
                            ON pt.provtype = CAST(s1.q107 AS CHAR) 
                        LEFT JOIN zilla z 
                            ON z.zillaid = s1.zillaid 
                        LEFT JOIN upazila u 
                            ON u.zillaid = s1.zillaid 
                            AND u.upazilaid = s1.upazilaid 
                        LEFT JOIN unions un 
                            ON un.zillaid = s1.zillaid 
                            AND un.upazilaid = s1.upazilaid 
                            AND un.unionid = s1.unionid 
                        LEFT JOIN cluster c 
                            ON c.clusterid = CAST(s2.q211 AS CHAR) 
                        WHERE s2.idno IS NOT NULL";

        $queryRadio = "SELECT
                        CONCAT(
                        UPPER(SUBSTRING(u.upazilanameeng, 1, 1)), LOWER(SUBSTRING(u.upazilanameeng, 2)), 
                        ', ' , 
                        UPPER(SUBSTRING(z.zillanameeng, 1, 1)), LOWER(SUBSTRING(z.zillanameeng, 2))
                        ) AS Area,
                        SUM(CASE
                                WHEN s2.q203 = 2
                                    AND s2.q205b = 2
                                    AND s2.q205c = 2
                                    AND s2.q205d = 2
                                    AND (
                                        s1.q109 NOT LIKE '%test%' 
                                        OR s2.q201 NOT LIKE '%test%' 
                                        OR s2.q206a NOT LIKE '%test%' 
                                        OR s2.q206b NOT LIKE '%test%'
                                    ) 
                                THEN 1 
                                ELSE 0
                        END) AS 'Zero-dose',
                        SUM(CASE
                            WHEN s2.q203 = 2
                                AND s2.q205b = 1
                                AND (s2.q205c = 2 OR s2.q205d = 2) 
                                AND (s1.q109 NOT LIKE '%test%'
                                    OR s2.q201 NOT LIKE '%test%'
                                    OR s2.q206a NOT LIKE '%test%'
                                    OR s2.q206b NOT LIKE '%test%'
                                ) 
                            THEN 1 
                            ELSE 0
                        END) AS 'Under-immunized',

                        (COUNT(DISTINCT s1.idno) - 
                        SUM(CASE
                            WHEN s2.q205b = 2 
                                AND s2.q203 = 2 
                                AND (s1.q109 NOT LIKE '%test%' 
                                OR s2.q201 NOT LIKE '%test%' 
                                OR s2.q206a NOT LIKE '%test%' 
                                OR s2.q206b NOT LIKE '%test%') 
                            THEN 1 
                            ELSE 0
                        END) - 
                        SUM(CASE
                            WHEN s2.q203 = 2
                                AND s2.q205b = 1
                                AND s2.q205c = 2
                                AND s2.q205d = 2
                                AND (s1.q109 NOT LIKE '%test%'
                                    OR s2.q201 NOT LIKE '%test%'
                                    OR s2.q206a NOT LIKE '%test%'
                                    OR s2.q206b NOT LIKE '%test%'
                                ) 
                            THEN 1 
                            ELSE 0
                        END) 
                        ) AS 'Drop-out',

                        COUNT(DISTINCT s1.idno) AS 'Total_(ZD+UI)',
                        SUM(IF(s2.q212 IN (1, 2), 1, 0)) AS Vaccinated
                        FROM section_1_screening_checklist_idf s1 
                        JOIN section_2_vaccinations_info s2 
                            ON s2.idno = s1.idno
                        LEFT JOIN providerdb p 
                            ON p.providerid = CAST(s1.entryuser AS CHAR) 
                        LEFT JOIN providertype pt 
                            ON pt.provtype = CAST(s1.q107 AS CHAR) 
                        LEFT JOIN zilla z 
                            ON z.zillaid = s1.zillaid 
                        LEFT JOIN upazila u 
                            ON u.zillaid = s1.zillaid 
                            AND u.upazilaid = s1.upazilaid 
                        LEFT JOIN unions un 
                            ON un.zillaid = s1.zillaid 
                            AND un.upazilaid = s1.upazilaid 
                            AND un.unionid = s1.unionid 
                        LEFT JOIN cluster c 
                            ON c.clusterid = CAST(s2.q211 AS CHAR) 
                        WHERE s2.idno IS NOT NULL
                        GROUP BY z.zillaid, z.zillanameeng
                        UNION ALL
                        $queryUnion";

        $radio_query_result = $this->db->query($queryRadio);
        return $radio_query_result->result();
    }
    
    function eSupervision_summary_model()
    {
        $query_Union = "SELECT
                            'Total' AS `Upazilla`,
                            '' AS `Union`,
                            COUNT(DISTINCT s2.idno) AS 'Number_of_checklist_used'
                        FROM
                            section_1_identifications_ipc_reg s1
                        JOIN
                            section_1_manager_staff_service s2
                            ON s2.idno = s1.idno
                        LEFT JOIN
                            providerdb p
                            ON p.providerid = CAST(s1.entryuser AS UNSIGNED)
                        LEFT JOIN
                            providertype pt
                            ON pt.provtype = CAST(s1.q107 AS UNSIGNED)
                        LEFT JOIN
                            zilla z
                            ON z.zillaid = s1.zillaid
                        LEFT JOIN
                            upazila u
                            ON u.zillaid = s1.zillaid
                            AND u.upazilaid = s1.upazilaid
                        LEFT JOIN
                            unions un
                            ON un.zillaid = s1.zillaid
                            AND un.upazilaid = s1.upazilaid
                            AND un.unionid = s1.unionid
                        LEFT JOIN
                            cluster c
                            ON c.clusterid = CAST(s1.q106 AS UNSIGNED)
                        WHERE
                            s2.idno IS NOT NULL
                        ";

        $query_Radio = "SELECT
                            CONCAT(UPPER(SUBSTRING(u.upazilanameeng, 1, 1)), LOWER(SUBSTRING(u.upazilanameeng, 2))) AS `Upazilla`,
                            CONCAT(UPPER(SUBSTRING(un.unionnameeng, 1, 1)), LOWER(SUBSTRING(un.unionnameeng, 2))) AS `Union`,
                            COUNT(DISTINCT s2.idno) AS 'Number_of_checklist_used'
                        FROM
                            section_1_identifications_ipc_reg s1
                        JOIN
                            section_1_manager_staff_service s2
                            ON s2.idno = s1.idno
                        LEFT JOIN
                            providerdb p
                            ON p.providerid = CAST(s1.entryuser AS UNSIGNED)
                        LEFT JOIN
                            providertype pt
                            ON pt.provtype = CAST(s1.q107 AS UNSIGNED)
                        LEFT JOIN
                            zilla z
                            ON z.zillaid = s1.zillaid
                        LEFT JOIN
                            upazila u
                            ON u.zillaid = s1.zillaid
                            AND u.upazilaid = s1.upazilaid
                        LEFT JOIN
                            unions un
                            ON un.zillaid = s1.zillaid
                            AND un.upazilaid = s1.upazilaid
                            AND un.unionid = s1.unionid
                        LEFT JOIN
                            cluster c
                            ON c.clusterid = CAST(s1.q106 AS UNSIGNED)
                        WHERE
                            s2.idno IS NOT NULL
                        GROUP BY
                            un.unionid, u.upazilanameeng, un.unionnameeng
                        UNION ALL
                        $query_Union
                        ";

        $radio_query_result = $this->db->query($query_Radio);
        return $radio_query_result->result();
    }

    function userListingCount($searchText)
    {
        $this->db->select('BaseTbl.userId, BaseTbl.email, BaseTbl.name, BaseTbl.mobile, BaseTbl.isAdmin, BaseTbl.createdDtm, Role.role');
        $this->db->from('tbl_users as BaseTbl');
        $this->db->join('tbl_roles as Role', 'Role.roleId = BaseTbl.roleId', 'left');
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.email  LIKE '%" . $searchText . "%'
                            OR  BaseTbl.name  LIKE '%" . $searchText . "%'
                            OR  BaseTbl.mobile  LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        // $this->db->where('BaseTbl.roleId !=', 1);
        $query = $this->db->get();

        return $query->num_rows();
    }

    /**
     * This function is used to get the user listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function userListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.userId, BaseTbl.email, BaseTbl.name, BaseTbl.mobile, BaseTbl.isAdmin, BaseTbl.createdDtm, 
        Role.role, Role.status as roleStatus');
        $this->db->from('tbl_users as BaseTbl');
        $this->db->join('tbl_roles as Role', 'Role.roleId = BaseTbl.roleId', 'left');
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.email  LIKE '%" . $searchText . "%'
                            OR  BaseTbl.name  LIKE '%" . $searchText . "%'
                            OR  BaseTbl.mobile  LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        // $this->db->where('BaseTbl.roleId !=', 1);
        $this->db->order_by('BaseTbl.userId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();

        $result = $query->result();
        return $result;
    }

    /**
     * This function is used to get the user roles information
     * @return array $result : This is result of the query
     */
    function getUserRoles()
    {
        $this->db->select('roleId, role, status as roleStatus');
        $this->db->from('tbl_roles');
        $query = $this->db->get();

        return $query->result();
    }

}