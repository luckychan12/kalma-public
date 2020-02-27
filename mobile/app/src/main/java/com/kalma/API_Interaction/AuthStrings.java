package com.kalma.API_Interaction;

import android.content.Context;
import android.provider.Settings;

//Auth strings singleton
public class AuthStrings {
    private static AuthStrings authStrings;
    private static String authToken;
    private static String refreshToken;
    private static Context context;

    //private Constructor
    private AuthStrings(Context ctx){
        context = ctx;
        authToken = getAuthToken();
    }

    //return instance of class if already created or call constructor
    public static synchronized  AuthStrings getInstance(Context context){
        if (authStrings == null){
            authStrings = new AuthStrings(context);
        }
        return authStrings;
    }


    public void setAuthToken(String token){
        authToken = token;
    }

    public void setRefreshToken(String token){
        refreshToken = token;
    }


    public String getAuthToken() {
        return authToken;
    }

    public String getRefreshToken() {
        return refreshToken;
    }

    public void forgetAuthToken() {
        authToken = null;
    }

    public void forgetRefreshToken() {
        refreshToken = null;
    }

    public Long getDeviceToken(){
        return Long.parseLong(Settings.Secure.getString(context.getContentResolver(), Settings.Secure.ANDROID_ID),16);
    }


}

