<?php

namespace Nhd\Foundation\Services;

use GuzzleHttp\Client;

class LDAPService {

    private static $client = null;
    private static $data;

    public static function client() {
        if(self::$client == null) {
            self::$client = new Client([
                'base_uri' => config('auth.ldap_service_url'),
                'timeout' => config('auth.ldap_service_timeout'),
                'http_errors' => false,
                'verify' => false,
                'headers' => [
                    //'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]);

            self::getData();
        }

        return self::$client;
    }

    public static function getData()
    {
        $result = self::client()->post('/Auth', [
            'form_params' => [
                'username' => config('auth.ldap_service_username'),
                'password' => config('auth.ldap_service_password'),
            ]
        ]);

        if($result->getStatuscode() == 200) {
            $result = json_decode($result->getBody()->getContents());

            self::$data = $result->data ?? null;
        }
        else {
            self::$data = null;
        }
    }

    public static function call($method, $data = []) {
        self::client();

        if(!self::$data) {
            return null;
        }

        return self::$method($data);
    }

    private static function login($data)
    {
        $url = str_replace('{{host}}', '', self::$data->api->Users__Login->method_call_address);
        $request = self::client()->request('POST', $url, [
            'form_params' => [
                'netUserName' => $data['username'],
                'netPassword' => $data['password']
            ],
            'headers' => [
                'token' => self::$data->token,
                'apikey' => self::$data->api->Users__Login->method_api_key
            ]
        ]);

        if($request->getStatusCode() == 200) {
            $result = json_decode($request->getBody()->getContents());

            return $result->data ?? null;
        }

        return null;
    }

    private static function info($data)
    {
        $url = str_replace('{{host}}', '', self::$data->api->Users__GetInfo->method_call_address);
        $url = str_replace('{{user_name}}', $data['username'], $url);
        $request = self::client()->request('GET', $url, [
            'headers' => [
                'token' => self::$data->token,
                'apikey' => self::$data->api->Users__GetInfo->method_api_key
            ]
        ]);

        if($request->getStatusCode() == 200) {
            $result = json_decode($request->getBody()->getContents());

            return $result->data ?? null;
        }

        return null;
    }

    private static function infoByNationalCode($data)
    {
        $url = str_replace('{{host}}', '', self::$data->api->Common__PersonnelSearch->method_call_address);
        $url = str_replace('{{search_text}}', $data['national_code'], $url);
        $request = self::client()->request('GET', $url, [
            'headers' => [
                'token' => self::$data->token,
                'apikey' => self::$data->api->Common__PersonnelSearch->method_api_key
            ]
        ]);

        if($request->getStatusCode() == 200) {
            $result = json_decode($request->getBody()->getContents());

            return $result->data[0] ?? null;
        }

        return null;
    }

    private static function hierarchy($data)
    {
        $url = str_replace('{{host}}', '', self::$data->api->Common__SubPersonnelSearch->method_call_address);
        $url = str_replace('{{network_username}}', $data['network_username'], $url);
        $request = self::client()->request('GET', $url, [
            'headers' => [
                'token' => self::$data->token,
                'apikey' => self::$data->api->Common__SubPersonnelSearch->method_api_key
            ]
        ]);

        if($request->getStatusCode() == 200) {
            $result = json_decode($request->getBody()->getContents());

            return \Arr::pluck($result->data ?? [], 'network_username');
        }

        return [];
    }

    private static function getNetworkUsername($data)
    {
        $url = str_replace('{{host}}', '', self::$data->api->Common__NetworkSearch->method_call_address);
        $url = str_replace('{{search_text}}', $data['national_code'], $url);
        $request = self::client()->request('GET', $url, [
            'headers' => [
                'token' => self::$data->token,
                'apikey' => self::$data->api->Common__NetworkSearch->method_api_key
            ]
        ]);

        if($request->getStatusCode() == 200) {
            $result = json_decode($request->getBody()->getContents());

            return $result->data->records[0]->net_user_name ?? null;
        }

        return null;
    }

    private static function hadis()
    {
        $url = str_replace('{{host}}', '', self::$data->api->Common__Hadis->method_call_address);
        $request = self::client()->request('GET', $url, [
            'headers' => [
                'token' => self::$data->token,
                'apikey' => self::$data->api->Common__Hadis->method_api_key
            ]
        ]);

        if($request->getStatusCode() == 200) {
            $result = json_decode($request->getBody()->getContents());

            return $result->data ?? null;
        }

        return null;
    }
}