package com.kalma.Data;

import android.content.Context;
import android.content.SharedPreferences;
import android.provider.Settings;


import org.joda.time.DateTime;

import java.util.ArrayList;
import java.util.Dictionary;
import java.util.Hashtable;
import java.util.List;

//Auth strings singleton
public class AuthStrings {
    private static AuthStrings authStrings;
    private static String authToken;
    private static String refreshToken;
    private Hashtable links = new Hashtable(); ;
    private SharedPreferences settings;
    private static DateTime authTokenExp;
    private static DateTime refreshTokenExp;
    private static Context context;

    //private Constructor
    private AuthStrings(Context ctx){
        context = ctx.getApplicationContext();
        authToken = getAuthToken();
    }

    //return instance of class if already created or call constructor
    public static synchronized  AuthStrings getInstance(Context context){
        if (authStrings == null){
            authStrings = new AuthStrings(context);
        }
        return authStrings;
    }

    public void setLinks(Hashtable linksIn){
        links = linksIn;
    }

    public Hashtable getLinks(){
        return links;
    }

    public void setAuthToken(String token,DateTime exp){
        authToken = token;
        authTokenExp = exp;
    }

    public void setRefreshToken(String token, DateTime exp){
        refreshToken = token;
        refreshTokenExp = exp;
    }

    public DateTime getAuthExp(){
        return authTokenExp;
    }

    public String getAuthToken() {
        return authToken;
    }

    public String getRefreshToken() {
        return refreshToken;
    }

    public void forgetAuthToken() {
        authToken = "";
    }

    public void storeRefreshToken(){
        settings = context.getSharedPreferences("TOKENS",  0);
        SharedPreferences.Editor editor = settings.edit();
        editor.putString("RefreshToken", refreshToken);
        editor.apply();
    }

    public void forgetRefreshToken() {
        refreshToken = "";
        SharedPreferences settings = context.getSharedPreferences("TOKENS", 0);
        settings.edit().putString("RefreshToken", "").apply();
    }

    public void unstoreRefreshToken(){
        SharedPreferences settings = context.getSharedPreferences("TOKENS", 0);
        settings.edit().putString("RefreshToken", "").commit();
    }

    public Long getDeviceToken(){
        return Long.parseLong(Settings.Secure.getString(context.getContentResolver(), Settings.Secure.ANDROID_ID),16);
    }


}

