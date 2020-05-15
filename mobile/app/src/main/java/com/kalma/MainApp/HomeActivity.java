package com.kalma.MainApp;

import androidx.appcompat.app.AppCompatActivity;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.ProgressBar;
import android.widget.Toast;

import com.android.volley.VolleyError;
import com.kalma.API_Interaction.APICaller;
import com.kalma.API_Interaction.ServerCallback;
import com.kalma.Data.AuthStrings;
import com.kalma.Data.DataEntry;
import com.kalma.R;

import org.joda.time.DateTime;
import org.joda.time.format.DateTimeFormat;
import org.joda.time.format.DateTimeFormatter;
import org.joda.time.format.ISODateTimeFormat;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Map;
import java.util.Objects;

public class HomeActivity extends AppCompatActivity {
    @Override
    public void onBackPressed() {
        //Do nothing
    }
    Context context = this;
    Button buttonProfile,buttonSettings, buttonSleep, buttonCalm;
    ProgressBar progressBarSleep, progressBarCalm;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_home);
        setTitle("kalma Home");
        buttonProfile = findViewById(R.id.btnProfile);
        buttonProfile.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(context, UserProfileActivity.class);
                startActivity(intent);
            }
        });
        buttonSettings = findViewById(R.id.btnSettings);
        buttonSettings.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(context, SettingsActivity.class);
                startActivity(intent);
            }
        });

        buttonSleep = findViewById(R.id.btnSleepTracker);
        buttonSleep.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(context, SleepTrackerActivity.class);
                startActivity(intent);
            }
        });
        buttonCalm = findViewById(R.id.btnMindMins);
        buttonCalm.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(context, CalmTracker.class);
                startActivity(intent);
            }
        });
        progressBarCalm = findViewById(R.id.MindProgress);
        progressBarSleep = findViewById(R.id.SleepPorgress);
        getCalmData();
        getSleepData();
    }

    private void updateCalmProgress(DataEntry[] data){
        int percent = 0;
        for (DataEntry dataVal: data) {
            percent = percent + dataVal.getPercentage();
        }
        if (percent >= 100){
            percent = 100;
        }
        progressBarCalm.setProgress(percent);
    }

    private void updateSleepProgress(DataEntry[] data){
        int percent = 0;
        for (DataEntry dataVal: data) {
            percent = percent + dataVal.getPercentage();
        }
        if (percent >= 100){
            percent = 100;
        }
        progressBarSleep.setProgress(percent);
    }


    private Map buildMap() {
        Map<String, String> params = new HashMap<String, String>();
        params.put("Authorization", "Bearer " + AuthStrings.getInstance(getApplicationContext()).getAuthToken());
        return params;
    }


    private void getCalmData() {
        //create a json object and call API to log in
        DateTime last = (new DateTime()).withHourOfDay(0);
        String lastWeekStr = last.toString(DateTimeFormat.forPattern("yyyy-MM-dd"));

        String getLink = Objects.requireNonNull(AuthStrings.getInstance(getApplicationContext()).getLinks().get("calm")).toString() + "?from=" + lastWeekStr;
        String url = context.getResources().getString(R.string.api_url) + getLink;
        Log.i("REQUEST", url);
        APICaller apiCaller = new APICaller(context.getApplicationContext());
        apiCaller.getData( true,null, buildMap(), getLink, new ServerCallback() {
                    @Override
                    public void onSuccess(JSONObject response) {
                        // Log.e("RESPONSE", response.toString() );
                        try {
                            JSONArray periods = response.getJSONArray(  "periods");
                            DataEntry[] entries  = new DataEntry[periods.length()];
                            DateTimeFormatter parser = ISODateTimeFormat.dateTimeParser();
                            for (int i = 0; i < periods.length(); i++) {
                                int id = periods.getJSONObject(i).getInt("id");
                                DateTime startTime = parser.parseDateTime(periods.getJSONObject(i).getString("start_time"));
                                DateTime stopTime = parser.parseDateTime(periods.getJSONObject(i).getString("stop_time"));
                                int duration = periods.getJSONObject(i).getInt("duration");
                                Log.i("dur", Integer.toString(duration));
                                String description = periods.getJSONObject(i).getString("description");
                                DataEntry newEntry = new DataEntry(id, startTime, stopTime, duration, description);
                                newEntry.setDurationText(periods.getJSONObject(i).getString("duration_text"));
                                newEntry.setPercentage(periods.getJSONObject(i).getInt("progress_percentage"));
                                newEntry.setMessage(periods.getJSONObject(i).getString("progress_message"));
                                entries[i] = newEntry;
                            }
                            updateCalmProgress(entries);

                            Log.i("Response", response.toString());
                        } catch (JSONException je) {
                            Log.e("JSONException", "onErrorResponse: ", je);
                        }
                    }

                    @Override
                    public void onFail(VolleyError error) {
                        Log.e("ERROR", error.toString());
                        error.printStackTrace();
                        try {
                            //retrieve error message and display

                            String jsonInput = new String(Objects.requireNonNull(error.networkResponse.data, "utf-8"));
                            JSONObject responseBody = new JSONObject(jsonInput);
                            String message = responseBody.getString("message");
                            Log.e("Error.Response", responseBody.toString());
                            Toast toast = Toast.makeText(getApplicationContext(), message, Toast.LENGTH_LONG);
                            toast.show();
                        } catch (JSONException je) {
                            Log.e("JSONException", "onErrorResponse: ", je);
                        }
                        catch (NullPointerException ne){
                            Log.e("NULL POINTER ERROR", "ERROR");
                            error.printStackTrace();
                        }
                    }
                }
        );
    }


    private void getSleepData() {
        //create a json object and call API to log in
        DateTime last = (new DateTime()).minusDays(1).withHourOfDay(16);
        String lastWeekStr = last.toString(DateTimeFormat.forPattern("yyyy-MM-dd"));

        String getLink = Objects.requireNonNull(AuthStrings.getInstance(getApplicationContext()).getLinks().get("sleep")).toString() + "?from=" + lastWeekStr;
        String url = context.getResources().getString(R.string.api_url) + getLink;
        Log.i("REQUEST", url);
        APICaller apiCaller = new APICaller(context.getApplicationContext());
        apiCaller.getData( true,null, buildMap(), getLink, new ServerCallback() {
                    @Override
                    public void onSuccess(JSONObject response) {
                        // Log.e("RESPONSE", response.toString() );
                        try {
                            JSONArray periods = response.getJSONArray(  "periods");
                            DataEntry[] entries  = new DataEntry[periods.length()];
                            DateTimeFormatter parser = ISODateTimeFormat.dateTimeParser();
                            for (int i = 0; i < periods.length(); i++) {
                                int id = periods.getJSONObject(i).getInt("id");
                                DateTime startTime = parser.parseDateTime(periods.getJSONObject(i).getString("start_time"));
                                DateTime stopTime = parser.parseDateTime(periods.getJSONObject(i).getString("stop_time"));
                                int duration = periods.getJSONObject(i).getInt("duration");
                                Log.i("dur", Integer.toString(duration));
                                String description = "";
                                DataEntry newEntry = new DataEntry(id, startTime, stopTime, duration, description);
                                newEntry.setDurationText(periods.getJSONObject(i).getString("duration_text"));
                                newEntry.setPercentage(periods.getJSONObject(i).getInt("progress_percentage"));
                                newEntry.setMessage(periods.getJSONObject(i).getString("progress_message"));
                                entries[i] = newEntry;
                            }
                            updateSleepProgress(entries);

                            Log.i("Response", response.toString());
                        } catch (JSONException je) {
                            Log.e("JSONException", "onErrorResponse: ", je);
                        }
                    }

                    @Override
                    public void onFail(VolleyError error) {
                        Log.e("ERROR", error.toString());
                        error.printStackTrace();
                        try {
                            //retrieve error message and display

                            String jsonInput = new String(Objects.requireNonNull(error.networkResponse.data, "utf-8"));
                            JSONObject responseBody = new JSONObject(jsonInput);
                            String message = responseBody.getString("message");
                            Log.e("Error.Response", responseBody.toString());
                            Toast toast = Toast.makeText(getApplicationContext(), message, Toast.LENGTH_LONG);
                            toast.show();
                        } catch (JSONException je) {
                            Log.e("JSONException", "onErrorResponse: ", je);
                        }
                        catch (NullPointerException ne){
                            Log.e("NULL POINTER ERROR", "ERROR");
                            error.printStackTrace();
                        }
                    }
                }
        );
    }
}
