package com.kalma.MainApp;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;

import android.app.DatePickerDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.DatePicker;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.TimePicker;
import android.widget.Toast;

import com.android.volley.VolleyError;
import com.github.mikephil.charting.charts.LineChart;
import com.github.mikephil.charting.components.XAxis;
import com.github.mikephil.charting.data.Entry;
import com.github.mikephil.charting.utils.ViewPortHandler;
import com.kalma.API_Interaction.APICaller;
import com.kalma.API_Interaction.ServerCallback;
import com.kalma.Data.AuthStrings;
import com.kalma.Data.LineGraphEntry;
import com.kalma.Data.SleepDataEntry;
import com.kalma.R;

import org.joda.time.DateTime;
import org.joda.time.DateTimeZone;
import org.joda.time.Days;
import org.joda.time.Duration;
import org.joda.time.Interval;
import org.joda.time.LocalDate;
import org.joda.time.format.DateTimeFormat;
import org.joda.time.format.DateTimeFormatter;
import org.joda.time.format.ISODateTimeFormat;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.UnsupportedEncodingException;
import java.math.BigDecimal;
import java.math.RoundingMode;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.HashMap;
import java.util.List;
import java.util.Locale;
import java.util.Map;

public class SleepTrackerActivity extends AppCompatActivity {
    Context context = this;
    String link = AuthStrings.getInstance(context).getAccountLink();

    EditText txtStartDate, txtStopDate;
    Button buttonProfile, buttonSettings, buttonHome, buttonSend;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_sleep_tracker);

        link = link.replace("account", "sleep");
        setTitle("Sleep Tracker");
        final Calendar myCalendar = Calendar.getInstance();
        ((TimePicker)findViewById(R.id.startTimePicker)).setIs24HourView(true);
        ((TimePicker)findViewById(R.id.stopTimePicker)).setIs24HourView(true);
        Spinner spinner = (Spinner) findViewById(R.id.option_spinner);
        List<String> categories = new ArrayList<String>();
        for (int i = 5; i >= 1; i--) {
            categories.add(Integer.toString(i));
        }
        ArrayAdapter<String> dataAdapter = new ArrayAdapter<String>(this, R.layout.spinner_item, categories);
        dataAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        spinner.setAdapter(dataAdapter);

        txtStartDate = (EditText)findViewById(R.id.txtStartdate);
        txtStopDate = (EditText)findViewById(R.id.txtStopdate);
        final EditText[] selecting = {null};
        final DatePickerDialog.OnDateSetListener date = new DatePickerDialog.OnDateSetListener() {
            //Convert datepicker value into string and fill textbox
            @Override
            public void onDateSet(DatePicker view, int year, int monthOfYear,
                                  int dayOfMonth) {
                myCalendar.set(Calendar.YEAR, year);
                myCalendar.set(Calendar.MONTH, monthOfYear);
                myCalendar.set(Calendar.DAY_OF_MONTH, dayOfMonth);
                SimpleDateFormat sdf = new SimpleDateFormat("dd/MM/yyyy", Locale.UK);
                selecting[0].setText(sdf.format(myCalendar.getTime()));
            }
        };
        final DatePickerDialog datePicker = new DatePickerDialog(context);
        datePicker.setOnDateSetListener(date);
        txtStartDate.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                selecting[0] = txtStartDate;
                datePicker.show();
            }
        });
        txtStopDate.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                selecting[0] = txtStopDate;
                datePicker.show();
            }
        });
        datePicker.setOnShowListener(new DialogInterface.OnShowListener() {
            @Override
            public void onShow(DialogInterface arg0) {
                datePicker.getButton(AlertDialog.BUTTON_NEGATIVE).setTextColor(getResources().getColor(R.color.NoColour, null));
                datePicker.getButton(AlertDialog.BUTTON_POSITIVE).setTextColor(getResources().getColor(R.color.YesColour, null));
            }
        });


        buttonSend = findViewById(R.id.btnAddData);
        buttonSend.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                extractData();
            }
        });
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
        buttonHome = findViewById(R.id.btnHome);
        buttonHome.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(context, HomeActivity.class);
                startActivity(intent);
            }
        });


        LineChart chart = (LineChart) findViewById(R.id.chart);
        SleepDataEntry[] dataObjects = {new SleepDataEntry()};

        DateTimeFormatter formatter = DateTimeFormat.forPattern("dd/MM/yyy");
        DateTime today = new DateTime();
        DateTime lastWeek = today.minusWeeks(1).minusDays(1);
        lastWeek = lastWeek.withHourOfDay(16);
        getData(lastWeek);
    }




    private void getData(DateTime date) {
        //create a json object and call API to log in
        String lastWeekStr = date.toString(DateTimeFormat.forPattern("yyyy-MM-dd"));
        String getLink = link + "?from=" + lastWeekStr;
        Log.i("REQUEST", "getData: ");
        APICaller apiCaller = new APICaller(getApplicationContext());
        apiCaller.getData( true,null,buildMap(),getLink, new ServerCallback() {
                    @Override
                    public void onSuccess(JSONObject response) {
                        try {
                            JSONArray periods = response.getJSONArray(  "periods");
                            SleepDataEntry[] entries  = new SleepDataEntry[periods.length()];
                            DateTimeFormatter parser = ISODateTimeFormat.dateTimeParser();
                            for (int i = 0; i < periods.length(); i++) {
                                int id = periods.getJSONObject(i).getInt("id");
                                DateTime startTime = parser.parseDateTime(periods.getJSONObject(i).getString("start_time"));
                                DateTime stopTime = parser.parseDateTime(periods.getJSONObject(i).getString("stop_time"));
                                int duration = periods.getJSONObject(i).getInt("duration");
                                int rating = periods.getJSONObject(i).getInt("sleep_quality");
                                SleepDataEntry newEntry = new SleepDataEntry(id, startTime, stopTime, duration, rating);
                                newEntry.setDurationText(periods.getJSONObject(i).getString("duration_text"));
                                newEntry.setPercentage(periods.getJSONObject(i).getInt("progress_percentage"));
                                newEntry.setMessage(periods.getJSONObject(i).getString("progress_message"));
                                entries[i] = newEntry;
                            }
                            //graphData(entries);

                            Log.i("Response", response.toString());
                        } catch (JSONException je) {
                            Log.e("JSONException", "onErrorResponse: ", je);
                        }
                    }

                    @Override
                    public void onFail(VolleyError error) {
                        try {
                            //retrieve error message and display
                            String jsonInput = new String(error.networkResponse.data, "utf-8");
                            JSONObject responseBody = new JSONObject(jsonInput);
                            String message = responseBody.getString("message");
                            Log.e("Error.Response", responseBody.toString());
                            Toast toast = Toast.makeText(getApplicationContext(), message, Toast.LENGTH_LONG);
                            toast.show();
                        } catch (JSONException je) {
                            Log.e("JSONException", "onErrorResponse: ", je);
                        } catch (UnsupportedEncodingException err) {
                            Log.e("EncodingError", "onErrorResponse: ", err);
                        }
                    }
                }
        );

    }



    private void extractData() {
        Spinner spinner = (Spinner)findViewById(R.id.option_spinner);


        TimePicker startTimePicker = (TimePicker) findViewById(R.id.startTimePicker);
        DateTimeFormatter formatter = DateTimeFormat.forPattern("dd/MM/yyy");
        DateTime startDateTime = new DateTime(formatter.parseDateTime(txtStartDate.getText().toString()), DateTimeZone.UTC)
                .withHourOfDay(startTimePicker.getHour())
                .withMinuteOfHour(startTimePicker.getMinute());


        TimePicker stopTimePicker = (TimePicker) findViewById(R.id.stopTimePicker);
        DateTime stopDateTime = new DateTime(formatter.parseDateTime(txtStartDate.getText().toString()), DateTimeZone.UTC)
                .withHourOfDay(startTimePicker.getHour())
                .withMinuteOfHour(startTimePicker.getMinute());

        int rating = Integer.parseInt(spinner.getSelectedItem().toString());
        String startISO8601 = startDateTime.toString(DateTimeFormat.forPattern("yyyy-MM-dd'T'HH:mm:ss"));
        String stopISO68601 = stopDateTime.toString(DateTimeFormat.forPattern("yyyy-MM-dd'T'HH:mm:ss"));

        JSONObject object = null;
        try {
            object = createSendObject(rating, startISO8601, stopISO68601);
        }catch (JSONException e){
            e.printStackTrace();
        }
        sendCreate(object);
    }

    private JSONObject createSendObject(int rating, String startISO8601, String stopISO68601) throws JSONException {
        JSONObject entry = new JSONObject();
        entry.put("start_time", startISO8601);
        entry.put("stop_time", stopISO68601);
        entry.put("sleep_quality",  rating);
        JSONArray periods = new JSONArray();
        periods.put(entry);
        JSONObject object = new JSONObject();
        object.put("periods", periods);
        return object;
    }

    private Map buildMap() {
        Map<String, String> params = new HashMap<String, String>();
        params.put("Authorization", "Bearer " + AuthStrings.getInstance(getApplicationContext()).getAuthToken());
        return params;
    }
    public void sendCreate(JSONObject body) {
        //create a json object and call API to log in


        APICaller apiCaller = new APICaller(getApplicationContext());
        apiCaller.post(true, body, buildMap(), link, new ServerCallback() {
                    @Override
                    public void onSuccess(JSONObject response) {
                        try {
                            //retrieve access token and store.
                            JSONObject responseBody = response;
                            Toast toast = Toast.makeText(getApplicationContext(), response.getString("message"), Toast.LENGTH_LONG);
                            toast.show();
                            Log.i("Response SEND DATA", response.toString());
                        } catch (JSONException je) {
                            Log.e("JSONException", "onErrorResponse: ", je);
                        }
                    }
                    @Override
                    public void onFail(VolleyError error) {
                        try {
                            //retrieve error message and display
                            String jsonInput = new String(error.networkResponse.data, "utf-8");
                            JSONObject responseBody = new JSONObject(jsonInput);
                            String message = responseBody.getString("message");
                            Log.w("Error.Response", jsonInput);
                            Toast toast = Toast.makeText(getApplicationContext(), message, Toast.LENGTH_LONG);
                            toast.show();
                        } catch (JSONException je) {
                            Log.e("JSONException", "onErrorResponse: ", je);
                        } catch (UnsupportedEncodingException err) {
                            Log.e("EncodingError", "onErrorResponse: ", err);
                        }
                    }
                }
        );

    }


}








