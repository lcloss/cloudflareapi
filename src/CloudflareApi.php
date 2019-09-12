<?php
/**
 * CloudflareApi -dev
 *
 * @author: Luciano Closs
 * This will work with Cloudflare API 4.0
 */
 
namespace LCloss\CloudflareApi;

class CloudflareAPI {
    protected $zoneId = '';
    protected $token = '';

    public function init($zoneId, $token)
    {
        $this->zoneId = $zoneId;
        $this->token = $token;
    }

    /* DnsRecords */
    public function addDnsRecord($data) 
    {
        $method = 'POST';
        $path = 'zones/' . $this->zoneId . '/dns_records';
        return $this->call($method, $path, $data);
    }

    public function deleteDnsRecord($type, $name, $content)
    {
        // First: find the correct id
        $result = $this->listAllDnsRecords();
        $res = '';
        $id = '';
        if ( $result['status'] == 'success' ) {
            $json = json_decode( $result['data'] );
            
            foreach($json->result as $record) {
                if ( $type == 'CNAME' && $record->type == 'CNAME' ) {
                    if ( $record->type == $type && $record->name == $name . '.' . $content ) {
                        $id = $record->id;
                        break;
                    }
                } else {
                    if ( $record->type == $type && $record->name == $name && $record->content == $content ) {
                        $id = $record->id;
                        break;
                    }
                }
            }
        }

        // Than, call delete id
        if ( $id != '' ) {
            $method = 'DELETE';
            $path = 'zones/' . $this->zoneId . '/dns_records/' . $id;
            return $this->call($method, $path);
        } else {
            return array(
                'status'    => 'error',
                'data'      => 'DNS Record not found'
            );
        }
    }

    public function listAllDnsRecords($data = array())
    {
        $page = 1;
        $continue = true;
        $json_arr = array();

        while( $continue ) {
            $result = $this->listDnsRecords( $data, $page );

            if ( $result['status'] == 'success' ) {
                $json = json_decode( $result['data'] );

                if ( count($json->result) == 0 ) {
                    $continue = false;
                } else {
                    $json_arr = array_merge( $json_arr, $json->result );
                }
            } else {
                $continue = false;
            }

            $page++;
        }
        $data = array(
            'status'    => 'success',
            'data'      => '{"result": ' . json_encode( $json_arr ) . '}'
        );

        return $data;
    }
    public function listDnsRecords($data = array(), $page = 1)
    {
        $method = 'GET';
        $path = 'zones/' . $this->zoneId . '/dns_records';
        $data['page'] = $page;

        return $this->call($method, $path, $data);
    }

    public function call($method, $path, $data = array())
    {
        if ( $method == 'GET' ) {
            $param_url = '';
            foreach($data as $key => $value) {
                if ( !empty($value) ) {
                    if ( $param_url == '') {
                        $param_url .= '?';
                    } else {
                        $param_url .= '&';
                    }
                    $param_url .= $key . '=' . $value;
                }
            }
            $param_json = "";
        } else {
            $parameters = array();
            foreach($data as $key => $value) {
                if ( !empty($value) ) {
                    $parameters[$key] = $value;
                }
            }
            if ( count($parameters) > 0 ) {
                $param_json = json_encode( $parameters );
            } else {
                $param_json = "";
            }
            $param_url = "";
        }

        $url = 'https://api.cloudflare.com/client/v4/' . $path . $param_url;
        $headers = array(
            "Authorization: Bearer " . $this->token,
            "Content-Type: application/json",
            "cache-control: no-cache"
        );

        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => $method,
          CURLOPT_POSTFIELDS => $param_json,
          CURLOPT_HTTPHEADER => $headers,
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
            return array(
                'status'    => 'error',
                'data'      => $err
            );
        } else {
            $json = json_decode( $response );
            if ( $json->success ) {
                return array(
                    'status'    => 'success',
                    'data'      => $response
                );
            } else {
                return array(
                    'status'    => 'error',
                    'data'      => $response
                );
            }
        }
    }
}