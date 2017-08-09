<?php
/**
 * Class Helper
 *
 * Contains the helper methods for the DM Instagram plugin
 * @package DmInstagram
 * @subpackage DmInstagram\Helpers
 */
class DmInstagram_Helpers_Helper {
    /**
     * Trim the response object to the number of needed image
     * @param object $data
     * @param int $numberOfImage
     * @return array
     */
    public function trimResults($data, $numberOfImage = 5) {
        // New holder
        $newData = array();
        // Make sure we received an array
        if(!is_array($data)) {
            // Check if an image
            if($data->type === 'image')
                $newData[] = $data;
        } else {
            // Process the array
            // Image counter
            $ctr = 0;
            foreach($data as $d) {
                // Check if an image
                if($d->type === 'image') {
                    // Add the object item in the array
                    $newData[] = $d;
                    // Increment the counter
                    $ctr++;
                    // Check if already have number of images needed
                    if($ctr >= $numberOfImage)
                        break;
                } // end if image
            } // end foreach
        } // end if is_array

        return $newData;
    }
}