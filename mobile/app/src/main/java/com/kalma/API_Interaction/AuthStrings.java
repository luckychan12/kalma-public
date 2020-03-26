package com.kalma.API_Interaction;

import android.content.Context;
import android.content.SharedPreferences;
import android.preference.PreferenceManager;
import android.provider.Settings;
import android.security.keystore.KeyGenParameterSpec;
import android.security.keystore.KeyProperties;


import java.io.BufferedWriter;
import java.io.File;
import java.io.IOException;
import java.io.OutputStreamWriter;
import java.security.GeneralSecurityException;
import java.util.prefs.PreferenceChangeEvent;

import javax.crypto.KeyGenerator;
import javax.crypto.SecretKey;

//Auth strings singleton
public class AuthStrings {
    private static AuthStrings authStrings;
    private static String authToken;
    private static String refreshToken;
    private static String accountLink;
    private static String LogoutLink;
    private SharedPreferences settings;
    private static int authTokenExp;
    private static int refreshTokenExp;
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


    public void setAuthToken(String token,int exp){
        authToken = token;
        authTokenExp = exp;
    }

    public void setRefreshToken(String token, int exp){
        refreshToken = token;
        storeRefreshToken();
        refreshTokenExp = exp;
    }

    public void setLogoutLink(String logoutLink) {
        LogoutLink = logoutLink;
    }

    public void setAccountLink(String accountLink) {
        AuthStrings.accountLink = accountLink;
    }

    public String getAccountLink(){
        return accountLink;
    }

    public String getLogoutLink() {
        return LogoutLink;
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

    private void storeRefreshToken(){
        settings = context.getSharedPreferences("TOKENS",  0);
        SharedPreferences.Editor editor = settings.edit();
        editor.putString("RefreshToken", refreshToken);
        editor.commit();
    }

    public void forgetRefreshToken() {
        refreshToken = "";
        SharedPreferences settings = context.getSharedPreferences("TOKENS", 0);
        settings.edit().putString("RefreshToken", "").commit();
    }

    public Long getDeviceToken(){
        return Long.parseLong(Settings.Secure.getString(context.getContentResolver(), Settings.Secure.ANDROID_ID),16);
    }


}

