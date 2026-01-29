<?php
require_once dirname(__DIR__)."/bootstrap.php";
header("Content-type: application/javascript; charset: UTF-8");
clog2ini("scripts.helper");
clog1seq(1);
?>
// ui event handlers 
function buttonAddDirectoryToTar_Clicked()
{
    var inputDirectoryToAddToTar = document.getElementById("inputDirectoryToAddToTar");
    var directoryToAddToTar = inputDirectoryToAddToTar.value;
    if (directoryToAddToTar != "")
    {
        var delimiter = "/";
        if (directoryToAddToTar.lastIndexOf(delimiter) < directoryToAddToTar.length - 1)
        {
            directoryToAddToTar += delimiter;
        }
         
        var entryForDirectory = new TarFileEntry.directory(directoryToAddToTar);
         
        var tarFile = Globals.Instance.tarFile;
         
        tarFile.entries.push(entryForDirectory);
    }
     
    this.refreshDivTarFile();
}
 
function buttonAddFileToTar_Clicked()
{
    var inputFileToAddToTar = document.getElementById("inputFileToAddToTar");
    var fileToLoad = inputFileToAddToTar.files[0];      
    if (fileToLoad != null)
    {
        FileHelper.loadFileAsBinaryString
        (
            fileToLoad,
            null, // contextForCallback
            buttonAddFileToTar_Clicked2 // callback
        );
    }
}
 
function buttonAddFileToTar_Clicked2(fileToAdd, fileToAddAsBinaryString)
{
    var selectDirectoryToAddFileTo = document.getElementById("selectDirectoryToAddFileTo");
    var directoryToAddFileTo = selectDirectoryToAddFileTo.selectedOptions[0].text;
    if (directoryToAddFileTo == "[root]")
    {
        directoryToAddFileTo = "";
    }
     
    var fileToAddName = directoryToAddFileTo + fileToAdd.name;
    var fileToAddAsBytes = ByteHelper.stringUTF8ToBytes(fileToAddAsBinaryString);
     
    var tarFile = Globals.Instance.tarFile;
     
    var headerToClone = tarFile.entries[0].header;
     
    var tarFileEntryHeader = new TarFileEntryHeader
    (
        fileToAddName,
        headerToClone.fileMode,
        headerToClone.userIDOfOwner,
        headerToClone.userIDOfGroup,
        fileToAddAsBytes.length, // fileSizeInBytes,
        headerToClone.timeModifiedInUnixFormat, // todo
        0, // checksum,
        TarFileTypeFlag.Instances.Normal,
        headerToClone.nameOfLinkedFile,
        headerToClone.uStarIndicator,
        headerToClone.uStarVersion,
        headerToClone.userNameOfOwner,
        headerToClone.groupNameOfOwner,
        headerToClone.deviceNumberMajor,
        headerToClone.deviceNumberMinor,
        headerToClone.filenamePrefix
    );
     
    tarFileEntryHeader.checksumCalculate();
     
    var entryForFileToAdd = new TarFileEntry
    (
        tarFileEntryHeader,
        fileToAddAsBytes
    );
     
    tarFile.entries.push(entryForFileToAdd);
     
    this.refreshDivTarFile();
}
 
function buttonSaveAsTar_Clicked()
{
    var tarFileToSave = Globals.Instance.tarFile;
    var inputFileNameToSaveAs = document.getElementById("inputFileNameToSaveAs");
    var fileNameToSaveAs = inputFileNameToSaveAs.value;
    tarFileToSave.downloadAs(fileNameToSaveAs);
}
 
function inputTarFileToLoad_Change(event)
{
    var fileToLoad = event.srcElement.files[0];     
    if (fileToLoad != null)
    {
        FileHelper.loadFileAsBinaryString
        (
            fileToLoad,
            null, // contextForCallback
            inputTarFileToLoad_Change2 // callback
        );
    }
}
 
function inputTarFileToLoad_Change2(fileToLoad, fileAsBinaryString)
{
    var fileName = fileToLoad.name;
    var fileAsBytes = ByteHelper.stringUTF8ToBytes(fileAsBinaryString);
    var tarFile = TarFile.fromBytes(fileName, fileAsBytes);
     
    Globals.Instance.tarFile = tarFile;
     
    this.refreshDivTarFile();
}

function refreshDivTarFile()
{
    var tarFile = Globals.Instance.tarFile;
    var tarFileAsDOMElement = DOMDisplayHelper.tarFileToDOMElement(tarFile);
    var divTarFile = document.getElementById("divTarFile");
    divTarFile.innerHTML = "";
    divTarFile.appendChild(tarFileAsDOMElement);    
     
    var selectDirectoryToAddFileTo = document.getElementById("selectDirectoryToAddFileTo");
    selectDirectoryToAddFileTo.innerHTML = "";
     
    var optionRoot = document.createElement("option");
    optionRoot.innerHTML = "[root]";
    selectDirectoryToAddFileTo.appendChild(optionRoot);
     
    var entriesForDirectories = tarFile.entriesForDirectories();    
    for (var i = 0; i < entriesForDirectories.length; i++)
    {
        var entry = entriesForDirectories[i];
        var option = document.createElement("option");
        option.innerHTML = entry.header.fileName;
        selectDirectoryToAddFileTo.appendChild(option);
    }
}
 
// extensions
 
function StringExtensions()
{
    // extension class
}
{
    String.prototype.padLeft = function(lengthToPadTo, characterToPadWith)
    {
        var result = this;
         
        if (characterToPadWith == null)
        {
            characterToPadWith = " ";
        }
     
        while (result.length < lengthToPadTo)
        {
            result = characterToPadWith + result;
        }
         
        return result;
    }
 
    String.prototype.padRight = function(lengthToPadTo, characterToPadWith)
    {
        var result = this;
         
        if (characterToPadWith == null)
        {
            characterToPadWith = " ";
        }
     
        while (result.length < lengthToPadTo)
        {
            result += characterToPadWith;
        }
         
        return result;
    }
}
 
 
// classes
 
function ByteHelper()
{}
{
    ByteHelper.BitsPerByte = 8;
    ByteHelper.BitsPerNibble = ByteHelper.BitsPerByte / 2;
    ByteHelper.ByteValueMax = Math.pow(2, ByteHelper.BitsPerByte) - 1;
 
    ByteHelper.bytesToStringHexadecimal = function(bytesToConvert)
    {
        var returnValue = "";
 
        var bitsPerNibble = ByteHelper.BitsPerNibble;
 
        for (var i = 0; i < bytesToConvert.length; i++)
        {
            var byte = bytesToConvert[i];
 
            for (var d = 1; d >= 0; d--)
            {
                var digitValue = byte >> (bitsPerNibble * d) & 0xF;
                var digitString = "";
                digitString += (digitValue < 10 ? digitValue : String.fromCharCode(55 + digitValue));
                returnValue += digitString;
            }
 
            returnValue += " ";
        }
 
        return returnValue;
    }
 
    ByteHelper.bytesToStringUTF8 = function(bytesToConvert)
    {
        var returnValue = "";
 
        for (var i = 0; i < bytesToConvert.length; i++)
        {
            var charCode = bytesToConvert[i];
            var character = String.fromCharCode(charCode);
            returnValue += character;
        }
 
        return returnValue;
    }
 
    ByteHelper.bytesToNumber = function(bytes)
    {
        var returnValue = 0;
 
        var bitsPerByte = ByteHelper.BitsPerByte;
 
        for (var i = 0; i < bytes.length; i++)
        {
            var byte = bytes[i];
            var byteValue = (byte << (bitsPerByte * i));
            returnValue += byteValue;
        }
 
        return returnValue;
    }
 
    ByteHelper.numberOfBytesNeededToStoreNumber = function(number)
    {
        var numberOfBitsInNumber = Math.ceil
        (
            Math.log(number + 1) / Math.log(2)
        );
 
        var numberOfBytesNeeded = Math.ceil
        (
            numberOfBitsInNumber 
            / ByteHelper.BitsPerByte
        );
 
        return numberOfBytesNeeded;
    }
 
    ByteHelper.numberToBytes = function(number, numberOfBytesToUse)
    {
        var returnValues = [];
 
        if (numberOfBytesToUse == null)
        {
            numberOfBytesToUse = this.numberOfBytesNeededToStoreNumber
            (
                number
            );
        }
 
        var bitsPerByte = ByteHelper.BitsPerByte;
 
        for (var i = 0; i < numberOfBytesToUse; i++)
        {
            var byte = (number >> (bitsPerByte * i)) & 0xFF;
            returnValues.push(byte);
        }
 
        return returnValues;
    }
 
    ByteHelper.stringUTF8ToBytes = function(stringToConvert)
    {
        var returnValues = [];
 
        for (var i = 0; i < stringToConvert.length; i++)
        {
            var charCode = stringToConvert.charCodeAt(i);
            returnValues.push(charCode);
        }
 
        return returnValues;
    }
 
    ByteHelper.xorBytesWithOthers = function(bytes0, bytes1)
    {
        for (var i = 0; i < bytes0.length; i++)
        {
            bytes0[i] ^= bytes1[i]; 
        }
 
        return bytes0;
    }
}
 
function ByteStream(bytes)
{
    this.bytes = bytes;  
 
    this.byteIndexCurrent = 0;
}
{
    // constants
 
    ByteStream.BitsPerByte = 8;
    ByteStream.BitsPerByteTimesTwo = ByteStream.BitsPerByte * 2;
    ByteStream.BitsPerByteTimesThree = ByteStream.BitsPerByte * 3;
 
    // instance methods
 
    ByteStream.prototype.hasMoreBytes = function()
    {
        return (this.byteIndexCurrent < this.bytes.length);
    }
     
    ByteStream.prototype.readBytes = function(numberOfBytesToRead)
    {
        var returnValue = [];
 
        for (var b = 0; b < numberOfBytesToRead; b++)
        {
            returnValue[b] = this.readByte();
        }
 
        return returnValue;
    }
 
    ByteStream.prototype.readByte = function()
    {
        var returnValue = this.bytes[this.byteIndexCurrent];
 
        this.byteIndexCurrent++;
 
        return returnValue;
    }
 
    ByteStream.prototype.readString = function(lengthOfString)
    {
        var returnValue = "";
 
        for (var i = 0; i < lengthOfString; i++)
        {
            var byte = this.readByte();
 
            if (byte != 0)
            {
                var byteAsChar = String.fromCharCode(byte);
                returnValue += byteAsChar;
            }
        }
 
        return returnValue;
    }
 
    ByteStream.prototype.writeBytes = function(bytesToWrite)
    {
        for (var b = 0; b < bytesToWrite.length; b++)
        {
            this.bytes.push(bytesToWrite[b]);
        }
 
        this.byteIndexCurrent = this.bytes.length;
    }
 
    ByteStream.prototype.writeByte = function(byteToWrite)
    {
        this.bytes.push(byteToWrite);
 
        this.byteIndexCurrent++;
    }
 
    ByteStream.prototype.writeString = function(stringToWrite, lengthPadded)
    {   
        for (var i = 0; i < stringToWrite.length; i++)
        {
            this.writeByte(stringToWrite.charCodeAt(i));
        }
         
        var numberOfPaddingChars = lengthPadded - stringToWrite.length;
        for (var i = 0; i < numberOfPaddingChars; i++)
        {
            this.writeByte(0);
        }
    }
}
 
function DOMDisplayHelper()
{
    // static class
}
{
    DOMDisplayHelper.tarFileEntryToDOMElement = function(tarFileEntry)
    {
        var returnValue = document.createElement("tr");
 
        var header = tarFileEntry.header;
         
        var td = document.createElement("td");
        td.innerHTML = header.fileName;
        returnValue.appendChild(td);
 
        var td = document.createElement("td");
        td.innerHTML = header.typeFlag.name;
        returnValue.appendChild(td);
 
        var td = document.createElement("td");
        td.innerHTML = header.fileSizeInBytes;
        returnValue.appendChild(td);
 
        var td = document.createElement("td");
 
        if (header.typeFlag.name == "Normal") {
            var buttonDownload = document.createElement("button");
            buttonDownload.innerHTML = "Download";
            buttonDownload.onclick = tarFileEntry.download.bind(tarFileEntry);
            td.appendChild(buttonDownload);
        }
        returnValue.appendChild(td);
        
        var td = document.createElement("td");
        var buttonDelete = document.createElement("button");
        buttonDelete.innerHTML = "Delete";
        buttonDelete.onclick = tarFileEntry.remove.bind(tarFileEntry);
        td.appendChild(buttonDelete);
        returnValue.appendChild(td);
        return returnValue;
    }
    
    DOMDisplayHelper.tarFileToDOMElement = function(tarFile)
    {
        var returnValue = document.createElement("div");
 
        var pFileName = document.createElement("p");
        pFileName.innerHTML = tarFile.fileName;
        returnValue.appendChild(pFileName);
 
        var tableEntries = document.createElement("table");
        tableEntries.style.border = "1px solid";
 
        var thead = document.createElement("thead");
 
        var th = document.createElement("th");
        th.innerHTML = "File Name";
        th.style.border = "1px solid";
        thead.appendChild(th);
 
        var th = document.createElement("th");
        th.innerHTML = "Type";
        th.style.border = "1px solid";
        thead.appendChild(th);
 
        th = document.createElement("th");
        th.innerHTML = "Size in Bytes";
        th.style.border = "1px solid";
        thead.appendChild(th);
 
        tableEntries.appendChild(thead);
 
        for (var i = 0; i < tarFile.entries.length; i++)
        {
            var entry = tarFile.entries[i];
            var domElementForEntry = DOMDisplayHelper.tarFileEntryToDOMElement(entry);
            tableEntries.appendChild(domElementForEntry);
        }
 
        returnValue.appendChild(tableEntries);
 
        return returnValue;
    }
     
}
 
function FileHelper()
{
    // static class
}
{
    FileHelper.destroyClickedElement = function(event)
    {
        document.body.removeChild(event.target);
    }
 
    FileHelper.loadFileAsBinaryString = function(fileToLoad, contextForCallback, callback)
    {   
        var fileReader = new FileReader();
        fileReader.onloadend = function(fileLoadedEvent)
        {
            var returnValue = null;
 
            if (fileLoadedEvent.target.readyState == FileReader.DONE)
            {
                returnValue = fileLoadedEvent.target.result;
            }
 
            callback.call
            (
                contextForCallback, 
                fileToLoad,
                returnValue
            );
        }
 
        fileReader.readAsBinaryString(fileToLoad);
    }
 
    FileHelper.saveBytesAsFile = function(bytesToWrite, fileNameToSaveAs)
    {
        var bytesToWriteAsArrayBuffer = new ArrayBuffer(bytesToWrite.length);
        var bytesToWriteAsUIntArray = new Uint8Array(bytesToWriteAsArrayBuffer);
        for (var i = 0; i < bytesToWrite.length; i++) 
        {
            bytesToWriteAsUIntArray[i] = bytesToWrite[i];
        }
 
        var bytesToWriteAsBlob = new Blob
        (
            [ bytesToWriteAsArrayBuffer ], 
            { type:"application/type" }
        );
 
        var downloadLink = document.createElement("a");
        downloadLink.download = fileNameToSaveAs;
        downloadLink.innerHTML = "Download File";
 
        downloadLink.href = window.URL.createObjectURL(bytesToWriteAsBlob);
        downloadLink.onclick = FileHelper.destroyClickedElement;
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);    
        downloadLink.click();
    }
}
 
function Globals()
{
    // do nothing
}
{
    Globals.Instance = new Globals();
}
 
// Based on specifications found at the URL
// https://en.wikipedia.org/wiki/Tar_file
 
function TarFile(fileName, entries)
{
    this.fileName = fileName;
    this.entries = entries;
}
{
    // constants
 
    TarFile.ChunkSize = 512;
 
    // static methods
 
    TarFile.fromBytes = function(fileName, bytes)
    {
        var reader = new ByteStream(bytes);
 
        var entries = [];
 
        var chunkSize = TarFile.ChunkSize;
 
        var numberOfConsecutiveZeroChunks = 0;
 
        while (reader.hasMoreBytes() == true)
        {
            var chunkAsBytes = reader.readBytes(chunkSize);
 
            var areAllBytesInChunkZeroes = true;
 
            for (var b = 0; b < chunkAsBytes.length; b++)
            {
                if (chunkAsBytes[b] != 0)
                {
                    areAllBytesInChunkZeroes = false;
                    break;
                }
            }
 
            if (areAllBytesInChunkZeroes == true)
            {
                numberOfConsecutiveZeroChunks++;
 
                if (numberOfConsecutiveZeroChunks == 2)
                {
                    break;
                }
            }
            else
            {
                numberOfConsecutiveZeroChunks = 0;
 
                var entry = TarFileEntry.fromBytes(chunkAsBytes, reader);
 
                entries.push(entry);
            }
        }
 
        var returnValue = new TarFile
        (
            fileName,
            entries
        );
 
        return returnValue;
    }
     
    TarFile.new = function(fileName)
    {
        return new TarFile
        (
            fileName,
            [] // entries
        );
    }
 
    // instance methods
     
    TarFile.prototype.downloadAs = function(fileNameToSaveAs)
    {   
        FileHelper.saveBytesAsFile
        (
            this.toBytes(),
            fileNameToSaveAs
        )
    }   
     
    TarFile.prototype.entriesForDirectories = function()
    {
        var returnValues = [];
         
        for (var i = 0; i < this.entries.length; i++)
        {
            var entry = this.entries[i];
            if (entry.header.typeFlag.name == "Directory")
            {
                returnValues.push(entry);
            }
        }
         
        return returnValues;
    }
     
    TarFile.prototype.toBytes = function()
    {
        var fileAsBytes = [];       
 
        // hack - For easier debugging.
        var entriesAsByteArrays = [];
         
        for (var i = 0; i < this.entries.length; i++)
        {
            var entry = this.entries[i];
            var entryAsBytes = entry.toBytes();
            entriesAsByteArrays.push(entryAsBytes);
        }       
         
        for (var i = 0; i < entriesAsByteArrays.length; i++)
        {
            var entryAsBytes = entriesAsByteArrays[i];
            fileAsBytes = fileAsBytes.concat(entryAsBytes);
        }
         
        var chunkSize = TarFile.ChunkSize;
         
        var numberOfZeroChunksToWrite = 2;
         
        for (var i = 0; i < numberOfZeroChunksToWrite; i++)
        {
            for (var b = 0; b < chunkSize; b++)
            {
                fileAsBytes.push(0);
            }
        }
 
        return fileAsBytes;
    }
     
    // strings
 
    TarFile.prototype.toString = function()
    {
        var newline = "\n";
 
        var returnValue = "[TarFile]" + newline;
 
        for (var i = 0; i < this.entries.length; i++)
        {
            var entry = this.entries[i];
            var entryAsString = entry.toString();
            returnValue += entryAsString;
        }
 
        returnValue += "[/TarFile]" + newline;
 
        return returnValue;
    }
}
 
function TarFileEntry(header, dataAsBytes)
{
    this.header = header;
    this.dataAsBytes = dataAsBytes;
}
{
    // methods
     
    // static methods
     
    TarFileEntry.directoryNew = function(directoryName)
    {
        var header = new TarFileEntryHeader.directoryNew(directoryName);
         
        var entry = new TarFileEntry(header, []);
         
        return entry;
    }
     
    TarFileEntry.fileNew = function(fileName, fileContentsAsBytes)
    {
        var header = new TarFileEntryHeader.fileNew(fileName, fileContentsAsBytes);
         
        var entry = new TarFileEntry(header, fileContentsAsBytes);
         
        return entry;
    }
     
    TarFileEntry.fromBytes = function(chunkAsBytes, reader)
    {
        var chunkSize = TarFile.ChunkSize;
     
        var header = TarFileEntryHeader.fromBytes
        (
            chunkAsBytes
        );
     
        var sizeOfDataEntryInBytesUnpadded = header.fileSizeInBytes;    
 
        var numberOfChunksOccupiedByDataEntry = Math.ceil
        (
            sizeOfDataEntryInBytesUnpadded / chunkSize
        )
     
        var sizeOfDataEntryInBytesPadded = 
            numberOfChunksOccupiedByDataEntry
            * chunkSize;
     
        var dataAsBytes = reader.readBytes
        (
            sizeOfDataEntryInBytesPadded
        ).slice
        (
            0, sizeOfDataEntryInBytesUnpadded
        );
     
        var entry = new TarFileEntry(header, dataAsBytes);
         
        return entry;
    }
     
    TarFileEntry.manyFromByteArrays = function(entriesAsByteArrays)
    {
        var returnValues = [];
         
        for (var i = 0; i < entriesAsByteArrays.length; i++)
        {
            var entryAsBytes = entriesAsByteArrays[i];
            var entry = TarFileEntry.fileNew
            (
                "File" + i, // hack - fileName
                entryAsBytes
            );
             
            returnValues.push(entry);
        }
         
        return returnValues;
    }
     
    // instance methods
 
    TarFileEntry.prototype.download = function(event)
    {
        FileHelper.saveBytesAsFile
        (
            this.dataAsBytes,
            this.header.fileName
        );
    }
     
    TarFileEntry.prototype.remove = function(event)
    {
        alert("Not yet implemented!"); // todo
    }
     
    TarFileEntry.prototype.toBytes = function()
    {
        var entryAsBytes = [];
     
        var chunkSize = TarFile.ChunkSize;
     
        var headerAsBytes = this.header.toBytes();
        entryAsBytes = entryAsBytes.concat(headerAsBytes);
         
        entryAsBytes = entryAsBytes.concat(this.dataAsBytes);
 
        var sizeOfDataEntryInBytesUnpadded = this.header.fileSizeInBytes;   
 
        var numberOfChunksOccupiedByDataEntry = Math.ceil
        (
            sizeOfDataEntryInBytesUnpadded / chunkSize
        )
     
        var sizeOfDataEntryInBytesPadded = 
            numberOfChunksOccupiedByDataEntry
            * chunkSize;
             
        var numberOfBytesOfPadding = 
            sizeOfDataEntryInBytesPadded - sizeOfDataEntryInBytesUnpadded;
     
        for (var i = 0; i < numberOfBytesOfPadding; i++)
        {
            entryAsBytes.push(0);
        }
         
        return entryAsBytes;
    }   
         
    // strings
     
    TarFileEntry.prototype.toString = function()
    {
        var newline = "\n";
 
        headerAsString = this.header.toString();
 
        var dataAsHexadecimalString = ByteHelper.bytesToStringHexadecimal
        (
            this.dataAsBytes
        );
 
        var returnValue = 
            "[TarFileEntry]" + newline
            + headerAsString
            + "[Data]"
            + dataAsHexadecimalString
            + "[/Data]" + newline
            + "[/TarFileEntry]"
            + newline;
 
        return returnValue
    }
     
}
 
function TarFileEntryHeader
(
    fileName,
    fileMode,
    userIDOfOwner,
    userIDOfGroup,
    fileSizeInBytes,
    timeModifiedInUnixFormat,
    checksum,
    typeFlag,
    nameOfLinkedFile,
    uStarIndicator,
    uStarVersion,
    userNameOfOwner,
    groupNameOfOwner,
    deviceNumberMajor,
    deviceNumberMinor,
    filenamePrefix
)
{
    this.fileName = fileName;
    this.fileMode = fileMode;
    this.userIDOfOwner = userIDOfOwner;
    this.userIDOfGroup = userIDOfGroup;
    this.fileSizeInBytes = fileSizeInBytes;
    this.timeModifiedInUnixFormat = timeModifiedInUnixFormat;
    this.checksum = checksum;
    this.typeFlag = typeFlag;
    this.nameOfLinkedFile = nameOfLinkedFile;
    this.uStarIndicator = uStarIndicator;
    this.uStarVersion = uStarVersion;
    this.userNameOfOwner = userNameOfOwner;
    this.groupNameOfOwner = groupNameOfOwner;
    this.deviceNumberMajor = deviceNumberMajor;
    this.deviceNumberMinor = deviceNumberMinor;
    this.filenamePrefix = filenamePrefix;
}
{
    TarFileEntryHeader.SizeInBytes = 500;
 
    // static methods
     
    TarFileEntryHeader.default = function()
    {
        var returnValue = new TarFileEntryHeader
        (
            "".padRight(100, "\0"), // fileName
            "100777 \0", // fileMode
            "0 \0".padLeft(8), // userIDOfOwner
            "0 \0".padLeft(8), // userIDOfGroup
            0, // fileSizeInBytes
            [49, 50, 55, 50, 49, 49, 48, 55, 53, 55, 52, 32], // hack - timeModifiedInUnixFormat
            0, // checksum
            TarFileTypeFlag.Instances.Normal,       
            "".padRight(100, "\0"), // nameOfLinkedFile,
            "".padRight(6, "\0"), // uStarIndicator,
            "".padRight(2, "\0"), // uStarVersion,
            "".padRight(32, "\0"), // userNameOfOwner,
            "".padRight(32, "\0"), // groupNameOfOwner,
            "".padRight(8, "\0"), // deviceNumberMajor,
            "".padRight(8, "\0"), // deviceNumberMinor,
            "".padRight(155, "\0") // filenamePrefix    
        );      
         
        return returnValue;
    }
     
    TarFileEntryHeader.directoryNew = function(directoryName)
    {
        var header = TarFileEntryHeader.default();
        header.fileName = directoryName;
        header.typeFlag = TarFileTypeFlag.Instances.Directory;
        header.fileSizeInBytes = 0;
        header.checksumCalculate();
         
        return header;
    }
     
    TarFileEntryHeader.fileNew = function(fileName, fileContentsAsBytes)
    {
        var header = TarFileEntryHeader.default();
        header.fileName = fileName;
        header.typeFlag = TarFileTypeFlag.Instances.Normal;
        header.fileSizeInBytes = fileContentsAsBytes.length;
        header.checksumCalculate();
         
        return header;
    }
 
    TarFileEntryHeader.fromBytes = function(bytes)
    {
        var reader = new ByteStream(bytes);
 
        var fileName = reader.readString(100).trim();
        var fileMode = reader.readString(8);
        var userIDOfOwner = reader.readString(8);
        var userIDOfGroup = reader.readString(8);
        var fileSizeInBytesAsStringOctal = reader.readString(12);
        var timeModifiedInUnixFormat = reader.readBytes(12);
        var checksumAsStringOctal = reader.readString(8);
        var typeFlagValue = reader.readString(1);
        var nameOfLinkedFile = reader.readString(100);
        var uStarIndicator = reader.readString(6);
        var uStarVersion = reader.readString(2);
        var userNameOfOwner = reader.readString(32);
        var groupNameOfOwner = reader.readString(32);
        var deviceNumberMajor = reader.readString(8);
        var deviceNumberMinor = reader.readString(8);
        var filenamePrefix = reader.readString(155);
        var reserved = reader.readBytes(12);
 
        var fileSizeInBytes = parseInt
        (
            fileSizeInBytesAsStringOctal.trim(), 8
        );
         
        var checksum = parseInt
        (
            checksumAsStringOctal, 8
        );      
         
        var typeFlags = TarFileTypeFlag.Instances._All;
        var typeFlagID = "_" + typeFlagValue;
        var typeFlag = typeFlags[typeFlagID];
 
        var returnValue = new TarFileEntryHeader
        (
            fileName,
            fileMode,
            userIDOfOwner,
            userIDOfGroup,
            fileSizeInBytes,
            timeModifiedInUnixFormat,
            checksum,
            typeFlag,
            nameOfLinkedFile,
            uStarIndicator,
            uStarVersion,
            userNameOfOwner,
            groupNameOfOwner,
            deviceNumberMajor,
            deviceNumberMinor,
            filenamePrefix
        );
 
        return returnValue;
    }
 
    // instance methods
     
    TarFileEntryHeader.prototype.checksumCalculate = function()
    {   
        var thisAsBytes = this.toBytes();
     
        // The checksum is the sum of all bytes in the header,
        // except we obviously can't include the checksum itself.
        // So it's assumed that all 8 of checksum's bytes are spaces (0x20=32).
        // So we need to set this manually.
                         
        var offsetOfChecksumInBytes = 148;
        var numberOfBytesInChecksum = 8;
        var presumedValueOfEachChecksumByte = " ".charCodeAt(0);
        for (var i = 0; i < numberOfBytesInChecksum; i++)
        {
            var offsetOfByte = offsetOfChecksumInBytes + i;
            thisAsBytes[offsetOfByte] = presumedValueOfEachChecksumByte;
        }
         
        var checksumSoFar = 0;
 
        for (var i = 0; i < thisAsBytes.length; i++)
        {
            var byteToAdd = thisAsBytes[i];
            checksumSoFar += byteToAdd;
        }       
 
        this.checksum = checksumSoFar;
         
        return this.checksum;
    }
     
    TarFileEntryHeader.prototype.toBytes = function()
    {
        var headerAsBytes = [];
        var writer = new ByteStream(headerAsBytes);
         
        var fileSizeInBytesAsStringOctal = (this.fileSizeInBytes.toString(8) + " ").padLeft(12, " ")
        var checksumAsStringOctal = (this.checksum.toString(8) + " \0").padLeft(8, " ");
 
        writer.writeString(this.fileName, 100);
        writer.writeString(this.fileMode, 8);
        writer.writeString(this.userIDOfOwner, 8);
        writer.writeString(this.userIDOfGroup, 8);
        writer.writeString(fileSizeInBytesAsStringOctal, 12);
        writer.writeBytes(this.timeModifiedInUnixFormat);
        writer.writeString(checksumAsStringOctal, 8);
        writer.writeString(this.typeFlag.value, 1);     
        writer.writeString(this.nameOfLinkedFile, 100);
        writer.writeString(this.uStarIndicator, 6);
        writer.writeString(this.uStarVersion, 2);
        writer.writeString(this.userNameOfOwner, 32);
        writer.writeString(this.groupNameOfOwner, 32);
        writer.writeString(this.deviceNumberMajor, 8);
        writer.writeString(this.deviceNumberMinor, 8);
        writer.writeString(this.filenamePrefix, 155);
        writer.writeString("".padRight(12, "\0")); // reserved
 
        return headerAsBytes;
    }       
         
    // strings
 
    TarFileEntryHeader.prototype.toString = function()
    {       
        var newline = "\n";
     
        var returnValue = 
            "[TarFileEntryHeader "
            + "fileName='" + this.fileName + "' "
            + "typeFlag='" + (this.typeFlag == null ? "err" : this.typeFlag.name) + "' "
            + "fileSizeInBytes='" + this.fileSizeInBytes + "' "
 
            /*
            + "fileMode='" + "[value]" + "' "
            + "userIDOfOwner='" + "[value]" + "' "
            + "userIDOfGroup='" + "[value]" + "' "
            + "timeModifiedInUnixFormat='" + "[value]" + "' "
            + "checksum='" + "[value]" + "' "
            + "nameOfLinkedFile='" + "[value]" + "' "
            + "uStarIndicator='" + "[value]" + "' "
            + "uStarVersion='" + "[value]" + "' "
            + "userNameOfOwner='" + "[value]" + "' "
            + "groupNameOfOwner='" + "[value]" + "' "
            + "deviceNumberMajor='" + "[value]" + "' "
            + "deviceNumberMinor='" + "[value]" + "' "
            + "filenamePrefix='" + "[value]" + "' "
            */
 
            + "]"
            + newline;
 
        return returnValue;
    }
}   
 
function TarFileTypeFlag(value, name)
{
    this.value = value;
    this.id = "_" + this.value;
    this.name = name;
}
{
    TarFileTypeFlag.Instances = new TarFileTypeFlag_Instances();
 
    function TarFileTypeFlag_Instances()
    {
        this.Normal         = new TarFileTypeFlag("0", "Normal");
        this.HardLink       = new TarFileTypeFlag("1", "Hard Link");
        this.SymbolicLink   = new TarFileTypeFlag("2", "Symbolic Link");
        this.CharacterSpecial   = new TarFileTypeFlag("3", "Character Special");
        this.BlockSpecial   = new TarFileTypeFlag("4", "Block Special");
        this.Directory      = new TarFileTypeFlag("5", "Directory");
        this.FIFO       = new TarFileTypeFlag("6", "FIFO");
        this.ContiguousFile     = new TarFileTypeFlag("7", "Contiguous File");
 
        // Additional types not implemented:
        // 'g' - global extended header with meta data (POSIX.1-2001)
        // 'x' - extended header with meta data for the next file in the archive (POSIX.1-2001)
        // 'A'–'Z' - Vendor specific extensions (POSIX.1-1988)
        // [other values] - reserved for future standardization
 
        this._All = 
        [
            this.Normal,
            this.HardLink,
            this.SymbolicLink,
            this.CharacterSpecial,
            this.BlockSpecial,
            this.Directory,
            this.FIFO,
            this.ContiguousFile,
        ];
 
        for (var i = 0; i < this._All.length; i++)
        {
            var item = this._All[i];
            this._All[item.id] = item;
        }
    }
}   
<?php
clog1seq(-1);
clog2end("scripts.helper");
