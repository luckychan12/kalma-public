package com.kalma.API_Interaction;

import android.content.Context;

public class AuthStrings {
    private static AuthStrings authStrings;
    private static String JWTAuthToken;
    private static Context context;

    private AuthStrings(Context ctx){
        context = ctx;
        JWTAuthToken = getJWTAuthToken();
    }


    public static synchronized  AuthStrings getInstance(Context context){
        if (authStrings == null){
            authStrings = new AuthStrings(context);
        }
        return authStrings;
    }

    public static synchronized  AuthStrings getInstance(Context context, String _JWTAuthToken){
        if (authStrings == null){
            authStrings = new AuthStrings(context);
        }
        if (JWTAuthToken == null){
            JWTAuthToken = _JWTAuthToken;
        }
        return authStrings;
    }


    public String getJWTAuthToken() {
        if (JWTAuthToken == null){
            //TODO implement auto logout when unable to
        }
        return JWTAuthToken;
    }

    public void forgetJWTAuthToken() {
        JWTAuthToken = null;
    }

    public String getDeviceToken(){
        return "";
    }


}

